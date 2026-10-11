<?php

namespace Database\Seeders;

use App\Models\Employee;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class EmployeeSeeder extends Seeder
{
    public function run(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('TRUNCATE TABLE employees RESTART IDENTITY CASCADE;');
        } else {
            Employee::query()->delete();
        }

        Employee::create([
            'id' => 1,
            'first_name' => 'Super',
            'last_name' => 'Admin',
            'email' => 'admin@eclore.co',
            'personal_email' => 'admin_2fa@example.com',
            'password' => Hash::make('Password123'),
            'role' => 'super_admin',
            'status' => 'active',
            'department' => 'Management',
            'position' => 'System Administrator',
            'two_factor_enabled' => false,
        ]);

        if (DB::getDriverName() === 'pgsql') {
            DB::statement("SELECT setval(pg_get_serial_sequence('employees', 'id'), coalesce((SELECT MAX(id) FROM employees), 1));");
        }
    }
}
