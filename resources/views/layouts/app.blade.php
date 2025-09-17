<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Timeline App') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:100,200,300,400,500,600,700,800,900&display=swap" rel="stylesheet" />

    <!-- Alpine.js -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Vis Timeline -->
    <script src="https://unpkg.com/vis-timeline@7.7.3/standalone/umd/vis-timeline-graph2d.min.js"></script>
    <link href="https://unpkg.com/vis-timeline@7.7.3/styles/vis-timeline-graph2d.min.css" rel="stylesheet" type="text/css">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @livewireStyles
</head>
<body class="font-sans antialiased bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-100 min-h-screen">
    <div id="app">
        @yield('content')
        {{ $slot ?? '' }}
    </div>

    @livewireScripts
</body>
</html>