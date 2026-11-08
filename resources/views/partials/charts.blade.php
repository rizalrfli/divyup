@if ($pie['total'] === 0 && empty($bars))
    <div class="panel">
        <h2 class="panel-title">Gambaran data</h2>
        <p class="empty">Belum ada aset yang bisa digambarkan. Angka akan muncul setelah tim mengunggah aset pertama.</p>
    </div>
@else
    <div class="chart-row">
        <section class="panel chart-panel">
            <div class="chart-head">
                <h2 class="panel-title">Aset masuk per bulan</h2>
                <p class="chart-note">Jumlah berkas yang diunggah tim, maksimal enam bulan terakhir.</p>
            </div>

            @if (empty($bars))
                <p class="empty">Belum ada aset yang diunggah.</p>
            @else
                <div class="bars" role="img"
                     aria-label="Jumlah aset per bulan: {{ collect($bars)->map(fn ($bar) => $bar['label'].' '.$bar['count'].' aset')->join(', ') }}">
                    @foreach ($bars as $bar)
                        <div class="bar-col">
                            <span class="bar-count">{{ $bar['count'] }}</span>
                            <div class="bar-track">
                                <div class="bar-fill" style="height: {{ max($bar['height'], 4) }}%"></div>
                            </div>
                            <span class="bar-label">{{ $bar['label'] }}</span>
                        </div>
                    @endforeach
                </div>
                <p class="chart-foot">
                    {{ collect($bars)->sum('count') }} aset pada {{ count($bars) }} bulan terakhir yang memiliki unggahan.
                </p>
            @endif
        </section>

        <section class="panel chart-panel">
            <div class="chart-head">
                <h2 class="panel-title">Distribusi status aset</h2>
                <p class="chart-note">Seluruh aset lintas proyek, {{ $pie['total'] }} berkas.</p>
            </div>

            @if ($pie['total'] === 0)
                <p class="empty">Belum ada aset.</p>
            @else
                <div class="pie-wrap">
                    <div class="pie" role="img"
                         aria-label="Distribusi status aset: {{ collect($pie['slices'])->map(fn ($slice) => $slice['label'].' '.$slice['percent'].' persen')->join(', ') }}"
                         style="background: conic-gradient({{ collect($pie['slices'])->map(fn ($slice) => $slice['color'].' '.$slice['from'].'% '.$slice['to'].'%')->join(', ') }});">
                        <div class="pie-hole">
                            <span class="pie-total">{{ $pie['total'] }}</span>
                            <span class="pie-total-label">aset</span>
                        </div>
                    </div>

                    <ul class="pie-legend">
                        @foreach ($pie['slices'] as $slice)
                            <li>
                                <span class="legend-dot" style="background: {{ $slice['color'] }}"></span>
                                <span class="legend-label">{{ $slice['label'] }}</span>
                                <span class="legend-value">{{ $slice['percent'] }}%</span>
                                <span class="legend-count">{{ $slice['count'] }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </section>
    </div>
@endif
