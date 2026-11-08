@php
    $revisi = \App\Models\Asset::where('status', \App\Enums\AssetStatus::Revisi->value)
        ->whereHas('project', fn ($query) => $query->where('team_id', auth()->id()))
        ->count();
@endphp

<p class="rail-label">Pekerjaan</p>
<a class="rail-link" href="{{ route('tim.dashboard') }}"
   @if (request()->routeIs('tim.dashboard')) aria-current="page" @endif>
    Tugas Saya
    @if ($revisi > 0)
        <span class="rail-badge">{{ $revisi }}</span>
    @endif
</a>
