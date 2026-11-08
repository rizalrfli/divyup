@if (! empty($stats))
    <section class="stat-row" aria-label="Ringkasan angka">
        @foreach ($stats as $stat)
            <div class="stat">
                <p class="stat-label">{{ $stat['label'] }}</p>
                <p class="stat-value">{{ $stat['value'] }}</p>
                @if (! empty($stat['note']))
                    <p class="stat-note">{{ $stat['note'] }}</p>
                @endif
            </div>
        @endforeach
    </section>
@endif
