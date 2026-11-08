<p class="rail-label">Kelola</p>
<a class="rail-link" href="{{ route('admin.dashboard') }}"
   @if (request()->routeIs('admin.dashboard')) aria-current="page" @endif>Dasbor</a>
<a class="rail-link" href="{{ route('admin.projects.index') }}"
   @if (request()->routeIs('admin.projects.*')) aria-current="page" @endif>Proyek</a>
<a class="rail-link" href="{{ route('admin.users') }}"
   @if (request()->routeIs('admin.users*')) aria-current="page" @endif>Pengguna</a>
<a class="rail-link" href="{{ route('admin.report') }}"
   @if (request()->routeIs('admin.report*')) aria-current="page" @endif>Rekap</a>

<p class="rail-label">Lainnya</p>
<a class="rail-link" href="{{ route('admin.log') }}"
   @if (request()->routeIs('admin.log')) aria-current="page" @endif>Log aktivitas</a>
