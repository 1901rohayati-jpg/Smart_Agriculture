<!doctype html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Dashboard IoT Smart Agriculture</title>
</head>
<body class="bg-slate-100 min-h-screen">
<header class="bg-white shadow p-4 flex flex-col md:flex-row md:items-center md:justify-between gap-2">
    <div>
        <h1 class="text-xl font-bold">Dashboard {{ $dashboard['device_id'] }}</h1>
        <p class="text-sm text-slate-500">Status:
            <span class="font-semibold {{ $dashboard['connection_status'] === 'online' ? 'text-green-600' : 'text-red-600' }}">
                {{ strtoupper($dashboard['connection_status']) }}
            </span>
        </p>
    </div>
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button class="bg-slate-700 text-white rounded-lg px-4 py-2">Logout</button>
    </form>
</header>

<main class="max-w-7xl mx-auto p-4 grid grid-cols-1 lg:grid-cols-3 gap-4">
    <section class="lg:col-span-2 space-y-4">
        <div class="bg-white rounded-xl p-4 shadow grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div><p class="text-sm text-slate-500">Nama Tumbuhan</p><p class="font-semibold">{{ $dashboard['plant_name'] }}</p></div>
            <div><p class="text-sm text-slate-500">Suhu</p><p class="font-semibold">{{ $dashboard['sensor_data']['temperature_c'] ?? '-' }} °C</p></div>
            <div><p class="text-sm text-slate-500">Kelembapan Tanah</p><p class="font-semibold">{{ $dashboard['sensor_data']['soil_moisture_pct'] ?? '-' }} %</p></div>
        </div>

        <div class="bg-white rounded-xl p-4 shadow">
            <h2 class="font-bold mb-3">History Penyiraman</h2>
            <a href="{{ route('history.export') }}" class="inline-block bg-emerald-600 text-white px-3 py-2 rounded mb-3">Export Excel</a>
            <div class="overflow-auto">
                <table class="w-full text-sm">
                    <thead><tr class="text-left border-b"><th>Mode</th><th>Start</th><th>Durasi</th><th>Soil Before</th></tr></thead>
                    <tbody>
                    @forelse($dashboard['history'] as $item)
                        <tr class="border-b"><td>{{ $item['mode'] ?? '-' }}</td><td>{{ $item['started_at'] ?? '-' }}</td><td>{{ $item['duration_sec'] ?? '-' }}s</td><td>{{ $item['soil_before'] ?? '-' }}%</td></tr>
                    @empty
                        <tr><td colspan="4" class="py-3 text-slate-500">Belum ada history.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <aside class="space-y-4">
        @if(session('success'))<div class="bg-green-100 text-green-700 p-3 rounded">{{ session('success') }}</div>@endif

        <form method="POST" action="{{ route('device.plant-name.update') }}" class="bg-white rounded-xl p-4 shadow space-y-2">
            @csrf @method('PUT')
            <h3 class="font-semibold">Ubah Nama Tumbuhan</h3>
            <input name="plant_name" class="w-full border rounded p-2" required>
            <button class="w-full bg-emerald-600 text-white rounded p-2">Simpan</button>
        </form>

        <form method="POST" action="{{ route('device.schedule.update') }}" class="bg-white rounded-xl p-4 shadow space-y-2">
            @csrf @method('PUT')
            <h3 class="font-semibold">Set Jam Siram Manual</h3>
            <input type="time" name="time" class="w-full border rounded p-2" required>
            <button class="w-full bg-blue-600 text-white rounded p-2">Kirim Jadwal</button>
        </form>

        <form method="POST" action="{{ route('device.wifi.update') }}" class="bg-white rounded-xl p-4 shadow space-y-2">
            @csrf @method('PUT')
            <h3 class="font-semibold">Konfigurasi WiFi Device</h3>
            <input name="ssid" class="w-full border rounded p-2" placeholder="SSID" required>
            <input name="password" class="w-full border rounded p-2" placeholder="Password" required>
            <button class="w-full bg-violet-600 text-white rounded p-2">Update WiFi</button>
        </form>

        <form method="POST" action="{{ route('device.manual-trigger') }}" class="bg-white rounded-xl p-4 shadow space-y-2">
            @csrf
            <h3 class="font-semibold">Manual Trigger Pompa</h3>
            <input type="number" min="1" max="60" name="duration_sec" value="10" class="w-full border rounded p-2" required>
            <button class="w-full bg-red-600 text-white rounded p-2">Siram Sekarang</button>
        </form>
    </aside>
</main>
</body>
</html>
