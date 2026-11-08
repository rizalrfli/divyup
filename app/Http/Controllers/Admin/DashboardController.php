<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AssetStatus;
use App\Enums\Role;
use App\Models\Asset;
use App\Models\Project;
use App\Models\User;
use Illuminate\View\View;

class DashboardController
{
    public function index(): View
    {
        $stats = [
            ['label' => 'Proyek aktif', 'value' => Project::where('status', 'aktif')->count(), 'note' => 'Seluruh klien'],
            ['label' => 'Menunggu persetujuan', 'value' => Asset::where('status', AssetStatus::Menunggu->value)->count(), 'note' => 'Aset di portal klien'],
            ['label' => 'Dalam revisi', 'value' => Asset::where('status', AssetStatus::Revisi->value)->count(), 'note' => 'Perlu dikerjakan tim'],
            ['label' => 'Total klien', 'value' => User::where('role', Role::Klien->value)->where('is_active', true)->count(), 'note' => 'Akun aktif'],
        ];

        $projects = Project::with('client', 'team', 'tasks')
            ->orderByDesc('due_date')
            ->get();

        $revisi = Asset::with('project.client')
            ->where('status', AssetStatus::Revisi->value)
            ->latest()
            ->limit(5)
            ->get();

        return view('admin.dashboard', [
            'stats' => $stats,
            'projects' => $projects,
            'revisi' => $revisi,
            'pie' => $this->assetStatusPie(),
            'bars' => $this->assetsPerMonth(),
        ]);
    }

    /**
     * Distribusi status aset. Persentase dibulatkan per irisan, bukan
     * dihitung ulang dari pembulatan, supaya legenda dan gambar sepakat.
     *
     * @return array{total: int, slices: array<int, array<string, mixed>>}
     */
    private function assetStatusPie(): array
    {
        $counts = Asset::query()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $total = (int) $counts->sum();

        $colors = [
            AssetStatus::Menunggu->value => '#E4732B',
            AssetStatus::Disetujui->value => '#0E7C7B',
            AssetStatus::Revisi->value => '#16233A',
        ];

        $slices = [];
        $offset = 0.0;

        foreach (AssetStatus::cases() as $status) {
            $count = (int) ($counts[$status->value] ?? 0);

            if ($count === 0) {
                continue;
            }

            $share = $count / $total * 100;

            $slices[] = [
                'label' => $status->label(),
                'count' => $count,
                'percent' => (int) round($share),
                'color' => $colors[$status->value],
                'from' => round($offset, 2),
                'to' => round($offset + $share, 2),
            ];

            $offset += $share;
        }

        return ['total' => $total, 'slices' => $slices];
    }

    /**
     * Jumlah aset masuk per bulan, hanya bulan yang punya unggahan,
     * maksimal enam bulan terakhir yang ada datanya.
     *
     * @return array<int, array{label: string, count: int, height: int}>
     */
    private function assetsPerMonth(): array
    {
        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

        $counts = Asset::query()
            ->select('created_at')
            ->get()
            ->groupBy(fn (Asset $asset) => $asset->created_at->format('Y-m'))
            ->map->count()
            ->sortKeys()
            ->take(-6);

        $max = (int) $counts->max();

        return $counts->map(function (int $count, string $key) use ($max, $months) {
            [$year, $month] = explode('-', $key);

            return [
                'label' => $months[(int) $month - 1].' '.$year,
                'count' => $count,
                'height' => $max > 0 ? (int) round($count / $max * 100) : 0,
            ];
        })->values()->all();
    }
}
