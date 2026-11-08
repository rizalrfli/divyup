<?php

namespace App\Http\Controllers\Admin;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController
{
    public function index(Request $request): View
    {
        $bulan = $request->query('bulan', now()->format('Y-m'));

        return view('admin.report', [
            'bulan' => $bulan,
            'projects' => $this->projectsFor($bulan),
        ]);
    }

    public function download(Request $request): StreamedResponse
    {
        $bulan = $request->query('bulan', now()->format('Y-m'));
        $projects = $this->projectsFor($bulan);

        return response()->streamDownload(function () use ($projects) {
            $out = fopen('php://output', 'w');

            fputcsv($out, ['proyek', 'klien', 'tim', 'tahap', 'progres', 'due_date']);

            foreach ($projects as $project) {
                fputcsv($out, [
                    $project->name,
                    $project->client->name,
                    $project->team?->name ?? '-',
                    $project->currentTask()?->stage->label() ?? '-',
                    $project->progress().'%',
                    $project->due_date->format('Y-m-d'),
                ]);
            }

            fclose($out);
        }, 'rekap-divyup-'.$bulan.'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /** @return \Illuminate\Database\Eloquent\Collection<int, Project> */
    private function projectsFor(string $bulan)
    {
        [$year, $month] = array_pad(explode('-', $bulan), 2, null);

        return Project::with('client', 'team', 'tasks')
            ->when($year && $month, fn ($query) => $query
                ->whereYear('due_date', (int) $year)
                ->whereMonth('due_date', (int) $month))
            ->orderBy('due_date')
            ->get();
    }
}
