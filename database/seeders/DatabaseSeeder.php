<?php

namespace Database\Seeders;

use App\Models\Memory;
use App\Models\Note;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $admin = User::query()->updateOrCreate([
            'email' => 'admin@gmail.com',
        ], [
            'name' => 'Admin',
            'role' => 'admin',
            'password' => Hash::make('admin@123'),
        ]);

        $userOne = User::query()->updateOrCreate([
            'email' => 'user@gmail.com',
        ], [
            'name' => 'first love',
            'role' => 'user',
            'password' => Hash::make('user123'),
        ]);

        $userTwo = User::query()->updateOrCreate([
            'email' => 'user2@gmail.com',
        ], [
            'name' => 'User Two',
            'role' => 'user',
            'password' => Hash::make('user456'),
        ]);

        Note::query()->firstOrCreate([
            'user_id' => $userOne->id,
            'title' => 'First Shared Note',
        ], [
            'description' => 'This is a seeded shared note for dashboard preview.',
        ]);

        Task::query()->firstOrCreate([
            'title' => 'Plan weekend trip',
            'assigned_to' => $userTwo->id,
            'created_by' => $userOne->id,
        ], [
            'description' => 'Discuss and finalize timeline together.',
            'status' => 'pending',
        ]);

        Memory::query()->firstOrCreate([
            'user_id' => $userOne->id,
            'title' => 'First Meeting',
        ], [
            'description' => 'The day we first met in person.',
            'type' => 'date',
            'memory_date' => now()->addDays(15)->toDateString(),
            'visibility' => 'shared',
        ]);
    }
}
