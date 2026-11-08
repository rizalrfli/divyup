<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'DivyUp') · DivyUp</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&family=Newsreader:opsz,wght@6..72,400;6..72,500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body class="app-body">
    <a class="skip-link" href="#konten">Lewati ke konten</a>

    <div class="app-shell">
        <aside class="rail">
            <div class="rail-top">
                <span class="wordmark">DIVYUP</span>
                <p class="rail-role">{{ auth()->user()->role->label() }}</p>
            </div>

            <nav class="rail-nav" aria-label="Menu utama">
                @yield('nav')
            </nav>

            <div class="rail-foot">
                <p class="rail-user">{{ auth()->user()->name }}</p>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-ghost btn-sm">Keluar</button>
                </form>
            </div>
        </aside>

        <main class="app-main" id="konten">
            <header class="page-head">
                <div>
                    <h1 class="page-title">@yield('heading', 'Dasbor')</h1>
                    @hasSection('subheading')
                        <p class="page-sub">@yield('subheading')</p>
                    @endif
                </div>
                @yield('actions')
            </header>

            @if (session('status'))
                <div class="flash" role="status">{{ session('status') }}</div>
            @endif

            @yield('content')
        </main>
    </div>
</body>
</html>
