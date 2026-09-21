<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            'super_admin' => 'Super Admin',
            'admin' => 'Admin',
            'principal' => 'Principal',
            'teacher' => 'Teacher',
            'student' => 'Student',
            'parent' => 'Parent',
            'staff' => 'Staff',
        ];

        foreach ($roles as $name => $label) {
            Role::updateOrCreate(['name' => $name], ['label' => $label]);
        }

        $permissions = [
            'students.view' => ['View Students', 'Student Management'],
            'students.create' => ['Add Students', 'Student Management'],
            'students.update' => ['Edit Students', 'Student Management'],
            'students.edit' => ['Edit Student Screens', 'Student Management'],
            'students.delete' => ['Delete Students', 'Student Management'],
            'teachers.view' => ['View Teachers', 'Teacher Management'],
            'teachers.create' => ['Add Teachers', 'Teacher Management'],
            'teachers.update' => ['Edit Teachers', 'Teacher Management'],
            'teachers.edit' => ['Edit Teacher Screens', 'Teacher Management'],
            'teachers.delete' => ['Delete Teachers', 'Teacher Management'],
            'attendance.view' => ['View Attendance', 'Attendance'],
            'attendance.mark' => ['Mark Attendance', 'Attendance'],
            'academic.manage' => ['Manage Academics', 'Academic'],
            'academics.view' => ['View Academics', 'Academic'],
            'academics.edit' => ['Edit Academics', 'Academic'],
            'classes.manage' => ['Manage Classes', 'Academic'],
            'subjects.manage' => ['Manage Subjects', 'Academic'],
            'announcements.manage' => ['Manage Announcements', 'Communication'],
            'announcements.view' => ['View Announcements', 'Communication'],
            'announcements.create' => ['Create Announcements', 'Communication'],
            'announcements.edit' => ['Edit Announcements', 'Communication'],
            'reports.view' => ['View Reports', 'Reports'],
            'users.manage' => ['Manage Users', 'System'],
            'roles.manage' => ['Manage Roles', 'System'],
        ];

        foreach ($permissions as $name => [$label, $group]) {
            Permission::updateOrCreate(compact('name'), compact('label', 'group'));
        }

        $allPermissions = Permission::pluck('id');
        Role::whereIn('name', ['super_admin', 'admin'])->each(fn (Role $role) => $role->permissions()->sync($allPermissions));
        Role::where('name', 'principal')->first()?->permissions()->sync(Permission::whereIn('name', ['students.view', 'students.create', 'students.update', 'teachers.view', 'attendance.view', 'academic.manage', 'announcements.manage', 'reports.view'])->pluck('id'));
        Role::where('name', 'teacher')->first()?->permissions()->sync(Permission::whereIn('name', ['students.view', 'teachers.view', 'attendance.view', 'attendance.mark'])->pluck('id'));
        Role::where('name', 'staff')->first()?->permissions()->sync(Permission::whereIn('name', ['students.view', 'teachers.view', 'reports.view'])->pluck('id'));
    }
}
