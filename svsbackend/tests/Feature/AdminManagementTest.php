<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_students_cannot_open_admin_management(): void
    {
        $student = User::factory()->create(['role' => User::ROLE_STUDENT]);

        $this->actingAs($student)->get(route('admin.dashboard'))->assertForbidden();
    }

    public function test_admin_can_create_and_filter_users(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this->actingAs($admin)->post(route('admin.users.store'), [
            'name' => 'School Principal',
            'email' => 'principal@svschools.edu',
            'phone' => '9876543210',
            'role' => User::ROLE_PRINCIPAL,
            'status' => 'active',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertRedirect(route('admin.users.index'));

        $this->assertDatabaseHas('users', [
            'email' => 'principal@svschools.edu',
            'role' => User::ROLE_PRINCIPAL,
            'status' => 'active',
        ]);

        $this->actingAs($admin)->get(route('admin.users.index', ['role' => User::ROLE_PRINCIPAL]))
            ->assertOk()
            ->assertSee('School Principal');
    }
}