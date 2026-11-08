<?php

namespace Database\Seeders;

use App\Enums\AssetStatus;
use App\Enums\Role;
use App\Enums\Stage;
use App\Enums\TaskStatus;
use App\Models\Asset;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $admin = User::create([
                'name' => 'Melany Fang Siau Ling',
                'email' => 'melany@divyup.test',
                'password' => 'password',
                'role' => Role::Admin,
            ]);

            $dedy = User::create([
                'name' => 'Dedy Bayu Setiawan',
                'email' => 'dedy@divyup.test',
                'password' => 'password',
                'role' => Role::Tim,
            ]);

            foreach (['Luthfi' => 'luthfi@divyup.test', 'Nimas' => 'nimas@divyup.test', "Jami'atul Afifah" => 'jamiatul@divyup.test'] as $name => $email) {
                User::create([
                    'name' => $name,
                    'email' => $email,
                    'password' => 'password',
                    'role' => Role::Tim,
                ]);
            }

            $kafe = User::create([
                'name' => 'Kafe Nusantara',
                'email' => 'kafe@divyup.test',
                'password' => 'password',
                'role' => Role::Klien,
            ]);

            User::create([
                'name' => 'Brand X',
                'email' => 'brandx@divyup.test',
                'password' => 'password',
                'role' => Role::Klien,
            ]);

            $project = Project::create([
                'name' => 'Social Media Management - Februari 2026',
                'client_id' => $kafe->id,
                'team_id' => $dedy->id,
                'start_date' => '2026-02-01',
                'due_date' => '2026-02-28',
            ]);

            $statuses = [
                Stage::Briefing->value => TaskStatus::Selesai,
                Stage::Desain->value => TaskStatus::Proses,
                Stage::Review->value => TaskStatus::Belum,
                Stage::Revisi->value => TaskStatus::Belum,
                Stage::SiapPublikasi->value => TaskStatus::Belum,
            ];

            foreach ($statuses as $stage => $status) {
                Task::create([
                    'project_id' => $project->id,
                    'stage' => $stage,
                    'status' => $status,
                    'completed_at' => $status === TaskStatus::Selesai ? Carbon::parse('2026-02-05 10:00') : null,
                ]);
            }

            $designTask = $project->tasks()->where('stage', Stage::Desain->value)->first();

            foreach (['feed-01.png', 'feed-02.png', 'story-01.png'] as $index => $name) {
                Asset::create([
                    'project_id' => $project->id,
                    'task_id' => $designTask->id,
                    'uploaded_by' => $dedy->id,
                    'name' => $name,
                    'path' => 'assets/'.$project->id.'/'.$name,
                    'type' => 'image/png',
                    'size_bytes' => 204800 + $index * 1024,
                    'status' => AssetStatus::Menunggu,
                ]);
            }

            $this->command->info("Admin: {$admin->email}, tim: {$dedy->email}, klien: {$kafe->email} (password: password)");
        });
    }
}
