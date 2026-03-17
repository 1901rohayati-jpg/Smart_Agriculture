# Smart Agriculture IoT (ESP32 + Laravel 11 + Firebase + MQTT)

Modul pembelajaran end-to-end untuk monitoring & kontrol penyiraman otomatis.

## 1) Struktur Penempatan Kode

```txt
Smart_Agriculture/
├── laravel-app/
│   ├── app/
│   │   ├── Exports/WateringHistoryExport.php
│   │   ├── Http/
│   │   │   ├── Controllers/
│   │   │   ├── Middleware/EnsureDeviceSession.php
│   │   │   └── Requests/
│   │   ├── Providers/AppServiceProvider.php
│   │   └── Services/
│   ├── bootstrap/app.php
│   ├── config/services.php
│   ├── resources/views/
│   └── routes/web.php
├── esp32/src/main.cpp
├── firebase/
│   ├── firebase_structure.json
│   └── firebase_rules.json
└── README.md
```

## 2) Backend Laravel 11 (MVC + Service Layer)

### A. Instalasi

```bash
cd laravel-app
composer install
cp .env.example .env
php artisan key:generate
php artisan serve
```

Tambahkan env Firebase pada `.env`:

- `FIREBASE_DATABASE_URL`
- `FIREBASE_AUTH_TOKEN`
- `FIREBASE_API_KEY`

### B. Dependency penting

- `guzzlehttp/guzzle` untuk REST call ke Firebase.
- `maatwebsite/excel` untuk export history penyiraman ke Excel.

### C. Alur Login Device ID

1. User buka `/` (form login Device ID).
2. Controller cek `device_id` ke Firebase via `DeviceService`.
3. Jika valid, simpan `session('device_id')`.
4. Semua route dashboard diproteksi middleware `device.session`.

### D. Validasi + Security

- Semua form menggunakan Form Request.
- Schedule wajib format `H:i` dengan rule `date_format:H:i`.
- Route command pakai throttle `throttle:30,1`.

## 3) Firebase Realtime Database

Gunakan root key per `device_id` pada `devices/{device_id}`.

Field utama:

- `plant_name`
- `sensor_data`
- `control_status`
- `last_seen` (Unix Epoch heartbeat)
- `history`
- `wifi_config`

Contoh lengkap ada di `firebase/firebase_structure.json`.

Security rule contoh ada di `firebase/firebase_rules.json`.

## 4) ESP32 (WiFi + MQTT + NTP + Fail-safe)

File firmware: `esp32/src/main.cpp`.

Fitur implementasi:

- Device ID hardcoded: `SMART_AGRICULTURE_001`
- MQTT topic pattern: `devices/{device_id}/...`
- Publish sensor data rate limit: maksimal 1 data / 5 detik
- Heartbeat online setiap 1 menit
- Subscribe:
  - `devices/{device_id}/schedule`
  - `devices/{device_id}/wifi_config`
  - `devices/{device_id}/manual_trigger`
- Fail-safe pompa: auto OFF maksimal 60 detik
- Reconnect otomatis jika WiFi/MQTT putus
- NTP untuk sinkronisasi waktu penyiraman

## 5) Mapping Topik MQTT

- Publish dari ESP32:
  - `devices/{device_id}/sensor_data`
  - `devices/{device_id}/heartbeat`
  - `devices/{device_id}/history`
- Subscribe di ESP32:
  - `devices/{device_id}/schedule`
  - `devices/{device_id}/manual_trigger`
  - `devices/{device_id}/wifi_config`

## 6) Catatan Integrasi Firebase Stream (Realtime)

Untuk kebutuhan realtime dari Laravel ke device, Laravel update path Firebase per `device_id`, lalu bridge/service MQTT-Firebase (jika dipakai di infrastruktur Anda) meneruskan payload ke topik device. Dengan pola ini, perubahan `plant_name`, `schedule`, atau `wifi_config` bisa disinkronkan hampir real-time.

## 7) QA Checklist

- [x] Middleware proteksi session device_id
- [x] Validasi semua input
- [x] Format jam `H:i`
- [x] Export Excel history
- [x] Rate limiting ESP32 (5 detik)
- [x] Heartbeat online/offline
- [x] Fail-safe pompa (<= 60 detik)
- [x] UI Tailwind responsive

