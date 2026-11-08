<?php

namespace Database\Seeders;

use App\Enums\AssetStatus;
use App\Enums\Role;
use App\Enums\Stage;
use App\Enums\TaskStatus;
use App\Models\ActivityLog;
use App\Models\ApprovalLog;
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

            $teams = [];
            foreach (['Luthfi' => 'luthfi@divyup.test', 'Nimas' => 'nimas@divyup.test', "Jami'atul Afifah" => 'jamiatul@divyup.test'] as $name => $email) {
                $teams[$name] = User::create([
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

            $brandX = User::create([
                'name' => 'Brand X',
                'email' => 'brandx@divyup.test',
                'password' => 'password',
                'role' => Role::Klien,
            ]);

            // Proyek berjalan: briefing selesai, desain dikerjakan, tiga aset menunggu review.
            $berjalan = $this->project($kafe, $dedy, 'Social Media Management - Februari 2026', '2026-02-01', '2026-02-28', [
                Stage::Briefing->value => TaskStatus::Selesai,
                Stage::Desain->value => TaskStatus::Proses,
                Stage::Review->value => TaskStatus::Belum,
                Stage::Revisi->value => TaskStatus::Belum,
                Stage::SiapPublikasi->value => TaskStatus::Belum,
            ]);

            foreach (['feed-01.png', 'feed-02.png', 'story-01.png'] as $index => $name) {
                $this->asset($berjalan, $dedy, $name, AssetStatus::Menunggu, '2026-02-10', 204800 + $index * 1024);
            }

            // Proyek selesai: semua tahap tuntas, aset sudah disetujui klien.
            $ramadan = $this->project($kafe, $teams['Luthfi'], 'Kampanye Ramadan 2026', '2026-01-05', '2026-02-20', [
                Stage::Briefing->value => TaskStatus::Selesai,
                Stage::Desain->value => TaskStatus::Selesai,
                Stage::Review->value => TaskStatus::Selesai,
                Stage::Revisi->value => TaskStatus::Dilewati,
                Stage::SiapPublikasi->value => TaskStatus::Selesai,
            ], 'selesai');

            foreach (['ramadan-feed-01.png', 'ramadan-reels-01.mp4'] as $index => $name) {
                $asset = $this->asset($ramadan, $teams['Luthfi'], $name, AssetStatus::Disetujui, '2026-01-20', 1048576 + $index * 2048);
                $this->approval($asset, $kafe, 'approved', '2026-01-22');
            }

            // Proyek baru masuk review: dua aset menunggu, satu sudah disetujui.
            $maret = $this->project($brandX, $teams['Nimas'], 'Social Media Management - Maret 2026', '2026-03-01', '2026-03-31', [
                Stage::Briefing->value => TaskStatus::Selesai,
                Stage::Desain->value => TaskStatus::Selesai,
                Stage::Review->value => TaskStatus::Proses,
                Stage::Revisi->value => TaskStatus::Belum,
                Stage::SiapPublikasi->value => TaskStatus::Belum,
            ]);

            $this->approval($this->asset($maret, $teams['Nimas'], 'maret-katalog-01.png', AssetStatus::Disetujui, '2026-03-06', 307200), $brandX, 'approved', '2026-03-07');

            foreach (['maret-katalog-02.png', 'maret-story-01.png'] as $name) {
                $this->asset($maret, $teams['Nimas'], $name, AssetStatus::Menunggu, '2026-03-08', 409600);
            }

            // Proyek dengan revisi terbuka: satu aset diminta revisi, satu disetujui.
            $peluncuran = $this->project($brandX, $dedy, 'Peluncuran Produk - April 2026', '2026-04-01', '2026-04-30', [
                Stage::Briefing->value => TaskStatus::Selesai,
                Stage::Desain->value => TaskStatus::Selesai,
                Stage::Review->value => TaskStatus::Proses,
                Stage::Revisi->value => TaskStatus::Proses,
                Stage::SiapPublikasi->value => TaskStatus::Belum,
            ]);

            $revisi = $this->asset($peluncuran, $dedy, 'peluncuran-hero.png', AssetStatus::Revisi, '2026-04-04', 512000);
            $revisi->update(['revision_note' => 'Tulisan tagline terlalu kecil, mohon dibesarkan dan ganti latar jadi krem.']);
            $this->approval($revisi, $brandX, 'revision', '2026-04-05');

            $this->approval($this->asset($peluncuran, $dedy, 'peluncuran-poster.png', AssetStatus::Disetujui, '2026-04-03', 256000), $brandX, 'approved', '2026-04-04');

            $this->command->info("Admin: {$admin->email}, tim: {$dedy->email}, klien: {$kafe->email} (password: password)");
            $this->command->info('4 proyek, 20 tahap, 10 aset, 3 klien-brand siap dipakai.');
        });
    }

    /**
     * @param  array<string, TaskStatus>  $statuses
     */
    private function project(User $client, User $team, string $name, string $start, string $due, array $statuses, string $status = 'aktif'): Project
    {
        $project = Project::create([
            'name' => $name,
            'client_id' => $client->id,
            'team_id' => $team->id,
            'start_date' => $start,
            'due_date' => $due,
            'status' => $status,
        ]);

        foreach ($statuses as $stage => $taskStatus) {
            Task::create([
                'project_id' => $project->id,
                'stage' => $stage,
                'status' => $taskStatus,
                'completed_at' => $taskStatus === TaskStatus::Selesai ? Carbon::parse($start)->addDays(3) : null,
            ]);
        }

        ActivityLog::create([
            'project_id' => $project->id,
            'actor_id' => $team->id,
            'action' => 'project.created',
            'detail' => 'Proyek dibuat dengan 5 tahapan',
        ]);

        return $project;
    }

    private function asset(Project $project, User $uploader, string $name, AssetStatus $status, string $date, int $bytes): Asset
    {
        $asset = Asset::create([
            'project_id' => $project->id,
            'task_id' => $project->tasks()->where('stage', Stage::Desain->value)->value('id'),
            'uploaded_by' => $uploader->id,
            'name' => $name,
            'path' => 'assets/'.$project->id.'/'.$name,
            'type' => str_ends_with($name, '.mp4') ? 'video/mp4' : 'image/png',
            'size_bytes' => $bytes,
            'status' => $status,
        ]);

        // created_at menentukan batang grafik bulanan, jadi diisi eksplisit.
        $asset->forceFill(['created_at' => Carbon::parse($date.' 10:00'), 'updated_at' => Carbon::parse($date.' 10:00')])->save();

        return $asset;
    }

    private function approval(Asset $asset, User $client, string $decision, string $date): void
    {
        $log = ApprovalLog::create([
            'asset_id' => $asset->id,
            'actor_id' => $client->id,
            'decision' => $decision,
            'note' => $asset->revision_note,
        ]);

        $log->forceFill(['created_at' => Carbon::parse($date.' 14:00'), 'updated_at' => Carbon::parse($date.' 14:00')])->save();
    }
}
