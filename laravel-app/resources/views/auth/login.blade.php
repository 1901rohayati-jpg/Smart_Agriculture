<!doctype html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Login Device - Smart Agriculture</title>
</head>
<body class="bg-slate-100 min-h-screen flex items-center justify-center p-4">
<div class="bg-white rounded-2xl shadow-lg p-6 w-full max-w-md">
    <h1 class="text-2xl font-bold mb-1">Smart Agriculture</h1>
    <p class="text-sm text-slate-500 mb-5">Login menggunakan Device ID unik dari ESP32.</p>

    @if($errors->any())
        <div class="bg-red-100 text-red-700 rounded-lg p-3 mb-4 text-sm">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('login.submit') }}" class="space-y-4">
        @csrf
        <div>
            <label class="block text-sm font-medium">Device ID</label>
            <input type="text" name="device_id" value="{{ old('device_id') }}" class="w-full rounded-lg border p-2" placeholder="SMART_AGRICULTURE_001" required>
        </div>
        <button class="w-full bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg py-2 font-semibold">Masuk Dashboard</button>
    </form>
</div>
</body>
</html>
