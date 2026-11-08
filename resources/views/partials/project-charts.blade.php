@if (empty($progressList) && empty($stageSpread))
    <div class="panel">
        <h2 class="panel-title">Gambaran proyek</h2>
        <p class="empty">Belum ada proyek aktif. Grafik akan muncul setelah admin membuat proyek pertama.</p>
    </div>
@else
    <div class="chart-row">
        <section class="panel chart-panel">
            <div class="chart-head">
                <h2 class="panel-title">Progres tiap proyek</h2>
                <p class="chart-note">Persentase tahap yang sudah tuntas pada proyek aktif, dari yang paling tertinggal.</p>
            </div>

            @if (empty($progressList))
                <p class="empty">Belum ada proyek aktif.</p>
            @else
                <ul class="progress-list">
                    @foreach ($progressList as $row)
                        <li class="progress-item">
                            <div class="progress-head">
                                <span class="progress-name">
                                    <a href="{{ route('admin.projects.show', $row['id']) }}">{{ $row['name'] }}</a>
                                    <span class="progress-client">{{ $row['client'] }}</span>
                                </span>
                                <span class="progress-value">
                                    {{ $row['percent'] }}%
                                    @if ($row['overdue'])
                                        <span class="tag tag-warn">Lewat tenggat</span>
                                    @endif
                                </span>
                            </div>
                            <div class="progress-track" role="img"
                                 aria-label="{{ $row['name'] }}: {{ $row['percent'] }} persen, {{ $row['done'] }} dari {{ $row['total'] }} tahap selesai">
                                <div class="progress-fill" style="width: {{ $row['percent'] }}%"></div>
                            </div>
                            <p class="progress-meta">{{ $row['done'] }} dari {{ $row['total'] }} tahap selesai</p>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>

        <section class="panel chart-panel">
            <div class="chart-head">
                <h2 class="panel-title">Sebaran proyek per tahap</h2>
                <p class="chart-note">Tahap yang sedang dikerjakan tiap proyek aktif, {{ count($progressList) }} proyek.</p>
            </div>

            @if (empty($stageSpread))
                <p class="empty">Belum ada proyek aktif.</p>
            @else
                <ul class="spread-list">
                    @foreach ($stageSpread as $row)
                        <li class="spread-item">
                            <div class="spread-head">
                                <span class="spread-label">{{ $row['label'] }}</span>
                                <span class="spread-count">{{ $row['count'] }} proyek</span>
                            </div>
                            <div class="spread-track" role="img"
                                 aria-label="{{ $row['label'] }}: {{ $row['count'] }} proyek">
                                <div class="spread-fill" style="width: {{ max($row['percent'], 4) }}%"></div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>
    </div>
@endif
