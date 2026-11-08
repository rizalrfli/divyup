@php
    $menunggu = \App\Models\Asset::where('status', \App\Enums\AssetStatus::Menunggu->value)
        ->whereHas('project', fn ($query) => $query->where('client_id', auth()->id()))
        ->count();
@endphp

<p class="rail-label">Proyek</p>
<a class="rail-link" href="{{ route('portal.dashboard') }}"
   @if (request()->routeIs('portal.dashboard')) aria-current="page" @endif>
    Proyek Saya
    @if ($menunggu > 0)
        <span class="rail-badge">{{ $menunggu }}</span>
    @endif
</a>

<p class="rail-label">Bantuan</p>
<p class="rail-note">Pertanyaan soal progres? Hubungi Account Manager DivyUp.</p>
