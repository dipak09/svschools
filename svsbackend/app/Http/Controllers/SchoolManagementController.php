<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\ActivityLog;
use App\Models\Announcement;
use App\Models\Attendance;
use App\Models\Exam;
use App\Models\ExamResult;
use App\Models\ParentModel;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SchoolManagementController extends Controller
{
    public function students(Request $request): View
    {
        $students = Student::with(['user', 'schoolClass', 'section', 'parents.user'])
            ->when($request->filled('search'), fn ($q) => $q->whereHas('user', fn ($u) => $u->where('name', 'like', '%'.$request->string('search')->toString().'%')->orWhere('email', 'like', '%'.$request->string('search')->toString().'%'))->orWhere('admission_number', 'like', '%'.$request->string('search')->toString().'%'))
            ->when($request->filled('class_id'), fn ($q) => $q->where('school_class_id', $request->integer('class_id')))
            ->latest()->paginate(15)->withQueryString();
        return view('admin.management', ['type' => 'students', 'title' => 'Student Management', 'students' => $students, 'classes' => SchoolClass::with(['academicYear', 'sections'])->orderBy('name')->get()]);
    }

    public function storeStudent(Request $request): RedirectResponse
    {
        $data = $request->validate($this->studentRules());
        DB::transaction(function () use ($data) {
            $user = User::create(['name' => $data['name'], 'email' => $data['email'], 'phone' => $data['phone'] ?? null, 'role' => User::ROLE_STUDENT, 'status' => 'active', 'password' => $data['password']]);
            Student::create(['user_id' => $user->id, ...collect($data)->except(['name', 'email', 'phone', 'password'])->toArray()]);
            $this->log('created', 'students', "Student {$user->name} was added.");
        });
        return back()->with('status', 'Student added successfully.');
    }

    public function showStudent(Student $student): View { return view('admin.profile', ['type' => 'student', 'record' => $student->load(['user', 'schoolClass', 'section', 'parents.user', 'attendances'])]); }
    public function editStudent(Student $student): View { return view('admin.profile', ['type' => 'student-edit', 'record' => $student->load('user'), 'classes' => SchoolClass::with('academicYear')->get()]); }

    public function updateStudent(Request $request, Student $student): RedirectResponse
    {
        $data = $request->validate($this->studentRules($student));
        DB::transaction(function () use ($data, $student) {
            $student->user->update(['name' => $data['name'], 'email' => $data['email'], 'phone' => $data['phone'] ?? null]);
            $student->update(collect($data)->except(['name', 'email', 'phone', 'password'])->toArray());
            $this->log('updated', 'students', "Student {$student->user->name} was updated.");
        });
        return back()->with('status', 'Student updated successfully.');
    }

    public function deleteStudent(Student $student): RedirectResponse
    {
        $student->user->update(['status' => 'inactive']);
        $this->log('deactivated', 'students', "Student {$student->user->name} was deactivated.");
        return back()->with('status', 'Student deactivated.');
    }

    public function teachers(): View
    {
        return view('admin.management', ['type' => 'teachers', 'title' => 'Teacher Management', 'teachers' => Teacher::with('user')->latest()->paginate(15), 'subjects' => Subject::where('is_active', true)->get(), 'classes' => SchoolClass::all()]);
    }

    public function storeTeacher(Request $request): RedirectResponse
    {
        $data = $request->validate(['name' => 'required|string|max:255', 'email' => 'required|email|unique:users,email', 'phone' => 'nullable|string|max:30', 'employee_id' => 'required|string|max:50|unique:teachers,employee_id', 'qualification' => 'nullable|string|max:255', 'department' => 'nullable|string|max:255', 'experience_years' => 'nullable|integer|min:0|max:60', 'joining_date' => 'nullable|date', 'password' => 'required|string|min:8']);
        DB::transaction(function () use ($data) { $user = User::create(['name' => $data['name'], 'email' => $data['email'], 'phone' => $data['phone'] ?? null, 'role' => User::ROLE_TEACHER, 'status' => 'active', 'password' => $data['password']]); Teacher::create(['user_id' => $user->id, ...collect($data)->except(['name', 'email', 'phone', 'password'])->toArray()]); $this->log('created', 'teachers', "Teacher {$user->name} was added."); });
        return back()->with('status', 'Teacher added successfully.');
    }

    public function updateTeacher(Request $request, Teacher $teacher): RedirectResponse
    {
        $data = $request->validate(['name' => 'required|string|max:255', 'email' => ['required', 'email', Rule::unique('users')->ignore($teacher->user_id)], 'phone' => 'nullable|string|max:30', 'qualification' => 'nullable|string|max:255', 'department' => 'nullable|string|max:255', 'experience_years' => 'nullable|integer|min:0|max:60', 'joining_date' => 'nullable|date']);
        $teacher->user->update(collect($data)->only(['name', 'email', 'phone'])->toArray()); $teacher->update(collect($data)->except(['name', 'email', 'phone'])->toArray());
        $this->log('updated', 'teachers', "Teacher {$teacher->user->name} was updated.");
        return back()->with('status', 'Teacher updated successfully.');
    }

    public function showTeacher(Teacher $teacher): View { return view('admin.teacher-profile', ['teacher' => $teacher->load(['user', 'subjects', 'classes'])]); }
    public function editTeacher(Teacher $teacher): View { return view('admin.profile', ['type' => 'teacher-edit', 'record' => $teacher->load('user')]); }

    public function parents(): View
    {
        return view('admin.management', ['type' => 'parents', 'title' => 'Parent Management', 'parents' => ParentModel::with(['user', 'students.user'])->latest()->paginate(15), 'students' => Student::with('user')->get()]);
    }

    public function storeParent(Request $request): RedirectResponse
    {
        $data = $request->validate(['name' => 'required|string|max:255', 'email' => 'required|email|unique:users,email', 'phone' => 'nullable|string|max:30', 'address' => 'nullable|string|max:500', 'password' => 'required|string|min:8', 'student_ids' => 'nullable|array', 'student_ids.*' => 'exists:students,id']);
        DB::transaction(function () use ($data) { $user = User::create(['name' => $data['name'], 'email' => $data['email'], 'phone' => $data['phone'] ?? null, 'role' => User::ROLE_PARENT, 'status' => 'active', 'password' => $data['password']]); $parent = ParentModel::create(['user_id' => $user->id, 'address' => $data['address'] ?? null]); $parent->students()->sync(collect($data['student_ids'] ?? [])->mapWithKeys(fn ($id) => [$id => ['relationship' => 'Guardian']])->all()); $this->log('created', 'parents', "Parent {$user->name} was added."); });
        return back()->with('status', 'Parent added successfully.');
    }

    public function showParent(ParentModel $parent): View { return view('admin.profile', ['type' => 'parent', 'record' => $parent->load(['user', 'students.user'])]); }
    public function editParent(ParentModel $parent): View { return view('admin.profile', ['type' => 'parent-edit', 'record' => $parent->load(['user', 'students']), 'students' => Student::with('user')->get()]); }
    public function updateParent(Request $request, ParentModel $parent): RedirectResponse
    {
        $data = $request->validate(['name' => 'required|string|max:255', 'email' => ['required', 'email', Rule::unique('users')->ignore($parent->user_id)], 'phone' => 'nullable|string|max:30', 'address' => 'nullable|string|max:500', 'student_ids' => 'nullable|array', 'student_ids.*' => 'exists:students,id']);
        $parent->user->update(collect($data)->only(['name', 'email', 'phone'])->toArray()); $parent->update(['address' => $data['address'] ?? null]); $parent->students()->sync(collect($data['student_ids'] ?? [])->mapWithKeys(fn ($id) => [$id => ['relationship' => 'Guardian']])->all()); $this->log('updated', 'parents', "Parent {$parent->user->name} was updated.");
        return to_route('admin.parents')->with('status', 'Parent updated successfully.');
    }

    public function classes(): View
    {
        return view('admin.management', ['type' => 'classes', 'title' => 'Classes & Sections', 'classes' => SchoolClass::with(['academicYear', 'teacher', 'sections.students'])->latest()->get(), 'years' => AcademicYear::latest()->get(), 'teachers' => User::where('role', User::ROLE_TEACHER)->get()]);
    }

    public function storeClass(Request $request): RedirectResponse
    {
        $data = $request->validate(['name' => 'required|string|max:100', 'academic_year_id' => 'required|exists:academic_years,id', 'class_teacher_id' => 'nullable|exists:users,id', 'section_name' => 'required|string|max:50']);
        $class = SchoolClass::create(collect($data)->except('section_name')->toArray()); $class->sections()->create(['name' => $data['section_name']]); $this->log('created', 'classes', "Class {$class->name} was added.");
        return back()->with('status', 'Class and section added.');
    }

    public function updateClass(Request $request, SchoolClass $class): RedirectResponse
    {
        $data = $request->validate(['name' => 'required|string|max:100', 'academic_year_id' => 'required|exists:academic_years,id', 'class_teacher_id' => 'nullable|exists:users,id']);
        $class->update($data); $this->log('updated', 'classes', "Class {$class->name} was updated.");
        return back()->with('status', 'Class updated.');
    }
    public function editClass(SchoolClass $class): View { return view('admin.structure-edit', ['type' => 'class', 'record' => $class, 'years' => AcademicYear::latest()->get(), 'teachers' => User::where('role', User::ROLE_TEACHER)->get()]); }

    public function deleteClass(SchoolClass $class): RedirectResponse
    {
        abort_if($class->students()->exists(), 422, 'Move students before deleting this class.');
        $class->delete(); $this->log('deleted', 'classes', 'Class was deleted.');
        return back()->with('status', 'Class deleted.');
    }

    public function storeSection(Request $request): RedirectResponse
    {
        $data = $request->validate(['school_class_id' => 'required|exists:school_classes,id', 'name' => 'required|string|max:50']);
        Section::create($data); $this->log('created', 'sections', "Section {$data['name']} was added.");
        return back()->with('status', 'Section added.');
    }

    public function updateSection(Request $request, Section $section): RedirectResponse
    {
        $data = $request->validate(['name' => 'required|string|max:50', 'school_class_id' => 'required|exists:school_classes,id']); $section->update($data); $this->log('updated', 'sections', 'Section was updated.');
        return back()->with('status', 'Section updated.');
    }
    public function editSection(Section $section): View { return view('admin.structure-edit', ['type' => 'section', 'record' => $section->load('schoolClass'), 'classes' => SchoolClass::all()]); }

    public function deleteSection(Section $section): RedirectResponse
    {
        abort_if($section->students()->exists(), 422, 'Move students before deleting this section.');
        $section->delete(); $this->log('deleted', 'sections', 'Section was deleted.'); return back()->with('status', 'Section deleted.');
    }

    public function assignTeacher(Request $request, Subject $subject): RedirectResponse
    {
        $data = $request->validate(['school_class_id' => 'required|exists:school_classes,id', 'teacher_id' => 'required|exists:users,id']);
        $subject->classes()->syncWithoutDetaching([$data['school_class_id'] => ['teacher_id' => $data['teacher_id']]]); $this->log('updated', 'teachers', 'Teacher assignment was updated.');
        return back()->with('status', 'Teacher assignment saved.');
    }

    public function subjects(): View
    {
        return view('admin.management', ['type' => 'subjects', 'title' => 'Subject Management', 'subjects' => Subject::with('classes')->latest()->get(), 'classes' => SchoolClass::all(), 'teachers' => User::where('role', User::ROLE_TEACHER)->get()]);
    }

    public function storeSubject(Request $request): RedirectResponse
    {
        $data = $request->validate(['name' => 'required|string|max:255', 'code' => 'required|string|max:30|unique:subjects,code', 'type' => 'required|string|max:50', 'class_ids' => 'nullable|array', 'class_ids.*' => 'exists:school_classes,id', 'teacher_id' => 'nullable|exists:users,id']);
        $subject = Subject::create(collect($data)->except(['class_ids', 'teacher_id'])->toArray()); $subject->classes()->sync(collect($data['class_ids'] ?? [])->mapWithKeys(fn ($id) => [$id => ['teacher_id' => $data['teacher_id'] ?? null]])->all()); $this->log('created', 'subjects', "Subject {$subject->name} was added.");
        return back()->with('status', 'Subject added successfully.');
    }
    public function deleteSubject(Subject $subject): RedirectResponse { $subject->delete(); $this->log('deleted', 'subjects', "Subject {$subject->name} was deleted."); return back()->with('status', 'Subject deleted.'); }
    public function editSubject(Subject $subject): View { return view('admin.subject-edit', ['subject' => $subject->load('classes'), 'classes' => SchoolClass::all(), 'teachers' => User::where('role', User::ROLE_TEACHER)->get()]); }
    public function updateSubject(Request $request, Subject $subject): RedirectResponse
    {
        $data = $request->validate(['name' => 'required|string|max:255', 'code' => ['required', 'string', 'max:30', Rule::unique('subjects', 'code')->ignore($subject)], 'type' => 'required|string|max:50', 'is_active' => 'required|boolean', 'class_ids' => 'array', 'class_ids.*' => 'exists:school_classes,id', 'teacher_id' => 'nullable|exists:users,id']);
        $subject->update(collect($data)->except(['class_ids', 'teacher_id'])->toArray()); $subject->classes()->sync(collect($data['class_ids'] ?? [])->mapWithKeys(fn ($id) => [$id => ['teacher_id' => $data['teacher_id'] ?? null]])->all()); $this->log('updated', 'subjects', "Subject {$subject->name} was updated."); return to_route('admin.subjects')->with('status', 'Subject updated.');
    }

    public function attendance(Request $request): View
    {
        $date = $request->date('date')?->toDateString() ?? now()->toDateString(); $students = collect();
        if ($request->filled('class_id')) { $students = Student::with(['user', 'section', 'attendances' => fn ($q) => $q->whereDate('attendance_date', $date)])->where('school_class_id', $request->integer('class_id'))->get(); }
        return view('admin.management', ['type' => 'attendance', 'title' => 'Attendance Management', 'classes' => SchoolClass::with('sections')->get(), 'students' => $students, 'date' => $date]);
    }

    public function storeAttendance(Request $request): RedirectResponse
    {
        $data = $request->validate(['attendance_date' => 'required|date', 'attendance' => 'required|array', 'attendance.*' => Rule::in(['present', 'absent', 'late'])]);
        foreach ($data['attendance'] as $studentId => $status) { Attendance::updateOrCreate(['student_id' => $studentId, 'attendance_date' => $data['attendance_date']], ['status' => $status, 'marked_by' => auth()->id()]); }
        $this->log('updated', 'attendance', 'Attendance was recorded for '.count($data['attendance']).' students.');
        return back()->with('status', 'Attendance saved successfully.');
    }

    public function attendanceHistory(Request $request): View
    {
        $history = Attendance::with(['student.user', 'student.schoolClass', 'student.section'])->when($request->filled('student_id'), fn ($q) => $q->where('student_id', $request->integer('student_id')))->when($request->filled('class_id'), fn ($q) => $q->whereHas('student', fn ($s) => $s->where('school_class_id', $request->integer('class_id'))))->when($request->filled('from'), fn ($q) => $q->whereDate('attendance_date', '>=', $request->date('from')))->when($request->filled('to'), fn ($q) => $q->whereDate('attendance_date', '<=', $request->date('to')))->latest('attendance_date')->paginate(30)->withQueryString();
        return view('admin.attendance-history', ['history' => $history, 'students' => Student::with('user')->get(), 'classes' => SchoolClass::all()]);
    }

    public function attendanceExport(Request $request)
    {
        $rows = Attendance::with('student.user')->when($request->filled('from'), fn ($q) => $q->whereDate('attendance_date', '>=', $request->date('from')))->when($request->filled('to'), fn ($q) => $q->whereDate('attendance_date', '<=', $request->date('to')))->get();
        return response()->streamDownload(function () use ($rows) { $out = fopen('php://output', 'w'); fputcsv($out, ['Student', 'Date', 'Status']); foreach ($rows as $row) { fputcsv($out, [$row->student->user->name, $row->attendance_date->toDateString(), $row->status]); } fclose($out); }, 'attendance-report.csv', ['Content-Type' => 'text/csv']);
    }

    public function academics(): View
    {
        return view('admin.management', ['type' => 'academics', 'title' => 'Academic Management', 'years' => AcademicYear::latest()->get(), 'exams' => Exam::with('academicYear')->latest()->get(), 'students' => Student::with('user')->get(), 'subjects' => Subject::all(), 'results' => ExamResult::with(['student.user', 'subject', 'exam'])->latest()->get()]);
    }

    public function storeAcademicYear(Request $request): RedirectResponse
    {
        $data = $request->validate(['name' => 'required|string|max:50|unique:academic_years,name', 'starts_on' => 'required|date', 'ends_on' => 'required|date|after:starts_on']); $year = AcademicYear::create($data); $this->log('created', 'academics', "Academic year {$year->name} was created."); return back()->with('status', 'Academic year created.');
    }

    public function updateAcademicYear(Request $request, AcademicYear $academicYear): RedirectResponse { $data = $request->validate(['name' => ['required', 'string', 'max:50', Rule::unique('academic_years', 'name')->ignore($academicYear)], 'starts_on' => 'required|date', 'ends_on' => 'required|date|after:starts_on']); $academicYear->update($data); $this->log('updated', 'academics', "Academic year {$academicYear->name} was updated."); return back()->with('status', 'Academic year updated.'); }
    public function deleteAcademicYear(AcademicYear $academicYear): RedirectResponse { abort_if($academicYear->exams()->exists(), 422, 'Delete exams before deleting this academic year.'); $academicYear->delete(); $this->log('deleted', 'academics', 'Academic year was deleted.'); return back()->with('status', 'Academic year deleted.'); }

    public function storeExam(Request $request): RedirectResponse
    {
        $data = $request->validate(['name' => 'required|string|max:255', 'academic_year_id' => 'required|exists:academic_years,id', 'starts_on' => 'nullable|date', 'ends_on' => 'nullable|date|after_or_equal:starts_on']); $exam = Exam::create($data); $this->log('created', 'academics', "Exam {$exam->name} was created."); return back()->with('status', 'Exam created.');
    }

    public function updateExam(Request $request, Exam $exam): RedirectResponse { $data = $request->validate(['name' => 'required|string|max:255', 'academic_year_id' => 'required|exists:academic_years,id', 'starts_on' => 'nullable|date', 'ends_on' => 'nullable|date|after_or_equal:starts_on']); $exam->update($data); $this->log('updated', 'academics', "Exam {$exam->name} was updated."); return back()->with('status', 'Exam updated.'); }
    public function deleteExam(Exam $exam): RedirectResponse { $exam->delete(); $this->log('deleted', 'academics', 'Exam was deleted.'); return back()->with('status', 'Exam deleted.'); }

    public function storeExamSchedule(Request $request): RedirectResponse
    {
        $data = $request->validate(['exam_id' => 'required|exists:exams,id', 'subject_id' => 'required|exists:subjects,id', 'scheduled_on' => 'required|date', 'starts_at' => 'nullable', 'ends_at' => 'nullable', 'room' => 'nullable|string|max:100']); \App\Models\ExamSchedule::updateOrCreate(['exam_id' => $data['exam_id'], 'subject_id' => $data['subject_id']], $data); $this->log('updated', 'academics', 'Exam schedule was saved.'); return back()->with('status', 'Exam schedule saved.');
    }

    public function deleteResult(ExamResult $result): RedirectResponse { $result->delete(); $this->log('deleted', 'academics', 'Exam result was deleted.'); return back()->with('status', 'Result deleted.'); }

    public function storeResult(Request $request): RedirectResponse
    {
        $data = $request->validate(['exam_id' => 'required|exists:exams,id', 'student_id' => 'required|exists:students,id', 'subject_id' => 'required|exists:subjects,id', 'marks' => 'required|numeric|min:0', 'total' => 'required|numeric|min:1']); ExamResult::updateOrCreate(collect($data)->only(['exam_id', 'student_id', 'subject_id'])->toArray(), collect($data)->only(['marks', 'total'])->toArray()); $this->log('updated', 'academics', 'Exam result was saved.'); return back()->with('status', 'Result saved.');
    }

    public function announcements(): View { return view('admin.management', ['type' => 'announcements', 'title' => 'Announcements', 'announcements' => Announcement::with('creator')->latest()->paginate(15), 'classes' => SchoolClass::all()]); }
    public function storeAnnouncement(Request $request): RedirectResponse { $data = $request->validate(['title' => 'required|string|max:255', 'body' => 'required|string', 'audience' => 'required|in:all,teachers,students,parents,class', 'school_class_id' => 'nullable|exists:school_classes,id']); Announcement::create($data + ['created_by' => auth()->id(), 'published_at' => $request->boolean('publish') ? now() : null]); $this->log('created', 'announcements', "Announcement {$data['title']} was created."); return back()->with('status', 'Announcement saved.'); }
    public function deleteAnnouncement(Announcement $announcement): RedirectResponse { $announcement->delete(); $this->log('deleted', 'announcements', 'Announcement was deleted.'); return back()->with('status', 'Announcement deleted.'); }

    public function updateAnnouncement(Request $request, Announcement $announcement): RedirectResponse { $data = $request->validate(['title' => 'required|string|max:255', 'body' => 'required|string', 'audience' => 'required|in:all,teachers,students,parents,class', 'school_class_id' => 'nullable|exists:school_classes,id']); $announcement->update($data); $this->log('updated', 'announcements', "Announcement {$announcement->title} was updated."); return back()->with('status', 'Announcement updated.'); }
    public function editAnnouncement(Announcement $announcement): View { return view('admin.announcement-edit', ['announcement' => $announcement, 'classes' => SchoolClass::all()]); }
    public function toggleAnnouncement(Announcement $announcement): RedirectResponse { $announcement->update(['published_at' => $announcement->published_at ? null : now()]); $this->log('updated', 'announcements', 'Announcement publish status changed.'); return back()->with('status', 'Announcement publish status updated.'); }

    public function reports(Request $request): View
    {
        $attendance = Attendance::query()->when($request->filled('from'), fn ($q) => $q->whereDate('attendance_date', '>=', $request->date('from')))->when($request->filled('to'), fn ($q) => $q->whereDate('attendance_date', '<=', $request->date('to')))->selectRaw("status, count(*) as total")->groupBy('status')->pluck('total', 'status');
        $results = ExamResult::with(['student.user', 'subject', 'exam'])->latest()->limit(50)->get();
        return view('admin.reports', ['attendance' => $attendance, 'results' => $results, 'students' => Student::with(['user', 'schoolClass'])->get(), 'teachers' => Teacher::with('user')->get(), 'users' => User::latest()->limit(50)->get()]);
    }

    public function reportExport(Request $request)
    {
        $users = User::query()->when($request->filled('role'), fn ($q) => $q->where('role', $request->string('role')->toString()))->get();
        return response()->streamDownload(function () use ($users) { $out = fopen('php://output', 'w'); fputcsv($out, ['Name', 'Email', 'Role', 'Status', 'Created']); foreach ($users as $user) { fputcsv($out, [$user->name, $user->email, $user->role, $user->status, $user->created_at]); } fclose($out); }, 'user-report.csv', ['Content-Type' => 'text/csv']);
    }

    public function activities(): View { return view('admin.management', ['type' => 'activities', 'title' => 'Activity Logs', 'activities' => ActivityLog::with('user')->latest()->paginate(30)]); }

    private function studentRules(?Student $student = null): array { return ['name' => 'required|string|max:255', 'email' => ['required', 'email', Rule::unique('users')->ignore($student?->user_id)], 'phone' => 'nullable|string|max:30', 'password' => [$student ? 'nullable' : 'required', 'string', 'min:8'], 'admission_number' => ['required', 'string', Rule::unique('students')->ignore($student)], 'date_of_birth' => 'nullable|date', 'gender' => 'nullable|string|max:30', 'address' => 'nullable|string|max:500', 'admission_date' => 'nullable|date', 'roll_number' => 'nullable|string|max:30', 'school_class_id' => 'nullable|exists:school_classes,id', 'section_id' => 'nullable|exists:sections,id']; }
    private function log(string $action, string $module, string $description): void { ActivityLog::create(['user_id' => auth()->id(), 'action' => $action, 'module' => $module, 'description' => $description]); }
}
