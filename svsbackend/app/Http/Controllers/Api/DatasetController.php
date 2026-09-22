<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\StudentRecord;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Open (unauthenticated) REST API.
 *
 * Every endpoint is scoped by {user_id} and every read is served through
 * Redis. Responses carry a `meta.cache` block so you can see whether the
 * payload came from Redis or from MySQL.
 */
class DatasetController extends Controller
{
    /** Cache lifetime for dataset pages, in seconds. */
    private const TTL = 300;

    /** Cache lifetime for the summary aggregate, in seconds. */
    private const SUMMARY_TTL = 600;

    private const MAX_PER_PAGE = 1000;

    /**
     * GET /api/v1/users/{user_id}/records
     *
     * The long demo dataset for one user, paginated and filterable.
     */
    public function index(Request $request, string $user_id): JsonResponse
    {
        $startedAt = microtime(true);

        if ($error = $this->rejectUnknownUser($user_id)) {
            return $error;
        }

        $filters = $this->validatedFilters($request);
        $cacheKey = $this->key($user_id, 'records', $filters);

        $cacheHit = true;

        $payload = $this->cache($user_id)->remember($cacheKey, self::TTL, function () use ($user_id, $filters, &$cacheHit) {
            $cacheHit = false;

            $paginator = $this->query((int) $user_id, $filters)
                ->paginate($filters['per_page'], ['*'], 'page', $filters['page']);

            return [
                'data' => $paginator->items(),
                'pagination' => [
                    'total' => $paginator->total(),
                    'per_page' => $paginator->perPage(),
                    'current_page' => $paginator->currentPage(),
                    'last_page' => $paginator->lastPage(),
                    'from' => $paginator->firstItem(),
                    'to' => $paginator->lastItem(),
                    'has_more' => $paginator->hasMorePages(),
                ],
            ];
        });

        return $this->respond($payload, $user_id, $cacheKey, $cacheHit, self::TTL, $filters, $startedAt);
    }

    /**
     * GET /api/v1/users/{user_id}/records/summary
     *
     * Aggregates over the same dataset — useful for dashboard widgets.
     */
    public function summary(Request $request, string $user_id): JsonResponse
    {
        $startedAt = microtime(true);

        if ($error = $this->rejectUnknownUser($user_id)) {
            return $error;
        }

        $cacheKey = $this->key($user_id, 'summary', []);
        $cacheHit = true;

        $payload = $this->cache($user_id)->remember($cacheKey, self::SUMMARY_TTL, function () use ($user_id, &$cacheHit) {
            $cacheHit = false;

            $totals = StudentRecord::where('user_id', $user_id)
                ->selectRaw('COUNT(*) AS total_records')
                ->selectRaw('ROUND(AVG(percentage), 2) AS avg_percentage')
                ->selectRaw('ROUND(AVG(attendance_percent), 2) AS avg_attendance')
                ->selectRaw('SUM(fees_total) AS fees_total')
                ->selectRaw('SUM(fees_paid) AS fees_paid')
                ->selectRaw('SUM(fees_due) AS fees_due')
                ->first();

            return [
                'totals' => [
                    'total_records' => (int) $totals->total_records,
                    'avg_percentage' => (float) $totals->avg_percentage,
                    'avg_attendance' => (float) $totals->avg_attendance,
                    'fees_total' => (float) $totals->fees_total,
                    'fees_paid' => (float) $totals->fees_paid,
                    'fees_due' => (float) $totals->fees_due,
                ],
                'by_standard' => $this->groupCount($user_id, 'standard'),
                'by_status' => $this->groupCount($user_id, 'status'),
                'by_grade' => $this->groupCount($user_id, 'grade'),
                'by_gender' => $this->groupCount($user_id, 'gender'),
            ];
        });

        return $this->respond($payload, $user_id, $cacheKey, $cacheHit, self::SUMMARY_TTL, [], $startedAt);
    }

    /**
     * DELETE /api/v1/users/{user_id}/cache
     *
     * Drops every cached Redis entry for this user.
     */
    public function flush(string $user_id): JsonResponse
    {
        if ($error = $this->rejectUnknownUser($user_id)) {
            return $error;
        }

        $this->cache($user_id)->flush();

        return response()->json([
            'success' => true,
            'message' => "Redis cache cleared for user #{$user_id}.",
            'user_id' => (int) $user_id,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Internals
    |--------------------------------------------------------------------------
    */

    /**
     * Redis cache bucket for one user. Tagging lets us flush a single user
     * without touching anyone else's cached pages.
     */
    private function cache(string $user_id)
    {
        return Cache::store('redis')->tags(["svs:user:{$user_id}"]);
    }

    /**
     * Deterministic cache key: same user + same filters => same key.
     */
    private function key(string $user_id, string $scope, array $filters): string
    {
        ksort($filters);

        return sprintf(
            'svs:v1:user:%s:%s:%s',
            $user_id,
            $scope,
            $filters === [] ? 'all' : md5(json_encode($filters))
        );
    }

    /**
     * 404 as JSON when the user_id is missing, non-numeric or unknown.
     */
    private function rejectUnknownUser(string $user_id): ?JsonResponse
    {
        if (! ctype_digit($user_id) || ! User::whereKey($user_id)->exists()) {
            return response()->json([
                'success' => false,
                'error' => 'user_not_found',
                'message' => "No user exists with id '{$user_id}'.",
            ], 404);
        }

        return null;
    }

    private function validatedFilters(Request $request): array
    {
        $validated = $request->validate([
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:' . self::MAX_PER_PAGE],
            'standard' => ['nullable', 'string', 'max:20'],
            'division' => ['nullable', 'string', 'max:5'],
            'status' => ['nullable', 'in:active,inactive,alumni'],
            'grade' => ['nullable', 'string', 'max:2'],
            'gender' => ['nullable', 'in:male,female,other'],
            'min_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'search' => ['nullable', 'string', 'max:100'],
            'sort_by' => ['nullable', 'in:id,student_name,percentage,attendance_percent,fees_due,admission_date'],
            'sort_dir' => ['nullable', 'in:asc,desc'],
        ]);

        return [
            'page' => (int) ($validated['page'] ?? 1),
            'per_page' => (int) ($validated['per_page'] ?? 50),
            'standard' => $validated['standard'] ?? null,
            'division' => $validated['division'] ?? null,
            'status' => $validated['status'] ?? null,
            'grade' => $validated['grade'] ?? null,
            'gender' => $validated['gender'] ?? null,
            'min_percentage' => isset($validated['min_percentage']) ? (float) $validated['min_percentage'] : null,
            'search' => $validated['search'] ?? null,
            'sort_by' => $validated['sort_by'] ?? 'id',
            'sort_dir' => $validated['sort_dir'] ?? 'asc',
        ];
    }

    private function query(int $user_id, array $filters)
    {
        return StudentRecord::query()
            ->where('user_id', $user_id)
            ->when($filters['standard'], fn ($q, $v) => $q->where('standard', $v))
            ->when($filters['division'], fn ($q, $v) => $q->where('division', $v))
            ->when($filters['status'], fn ($q, $v) => $q->where('status', $v))
            ->when($filters['grade'], fn ($q, $v) => $q->where('grade', $v))
            ->when($filters['gender'], fn ($q, $v) => $q->where('gender', $v))
            ->when($filters['min_percentage'], fn ($q, $v) => $q->where('percentage', '>=', $v))
            ->when($filters['search'], function ($q, $v) {
                $q->where(function ($inner) use ($v) {
                    $inner->where('student_name', 'like', "%{$v}%")
                        ->orWhere('roll_no', 'like', "%{$v}%")
                        ->orWhere('email', 'like', "%{$v}%");
                });
            })
            ->orderBy($filters['sort_by'], $filters['sort_dir']);
    }

    private function groupCount(string $user_id, string $column): array
    {
        return StudentRecord::where('user_id', $user_id)
            ->select($column, DB::raw('COUNT(*) AS total'))
            ->groupBy($column)
            ->orderBy($column)
            ->pluck('total', $column)
            ->toArray();
    }

    /**
     * Wrap any payload in the standard envelope, with cache diagnostics.
     */
    private function respond(array $payload, string $user_id, string $cacheKey, bool $cacheHit, int $ttl, array $filters, float $startedAt): JsonResponse
    {
        $body = [
            'success' => true,
            'user_id' => (int) $user_id,
        ] + $payload;

        $body['meta'] = [
            'cache' => [
                'store' => 'redis',
                'hit' => $cacheHit,
                'key' => $cacheKey,
                'ttl_seconds' => $ttl,
            ],
            'filters' => array_filter($filters, fn ($v) => $v !== null),
            'response_time_ms' => round((microtime(true) - $startedAt) * 1000, 2),
            'generated_at' => now()->toIso8601String(),
        ];

        return response()->json($body);
    }
}
