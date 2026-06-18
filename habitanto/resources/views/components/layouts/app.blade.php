<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Habitanto Fake Store Hub</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body class="min-h-screen bg-[radial-gradient(circle_at_top,_rgba(20,184,166,0.2),_transparent_40%),linear-gradient(180deg,_#fffaf2_0%,_#f3f7f7_45%,_#edf3f4_100%)] text-slate-900">
        {{ $slot }}
        @livewireScripts
    </body>
</html>
