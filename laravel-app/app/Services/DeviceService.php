<?php

namespace App\Services;

use Carbon\Carbon;

class DeviceService
{
    public function __construct(private readonly FirebaseService $firebase)
    {
    }

    public function authenticateByDeviceId(string $deviceId): bool
    {
        return !is_null($this->firebase->getDevice($deviceId));
    }

    public function getDashboard(string $deviceId): array
    {
        $device = $this->firebase->getDevice($deviceId) ?? [];
        $lastSeen = (int) ($device['last_seen'] ?? 0);

        return [
            'device_id' => $deviceId,
            'plant_name' => $device['plant_name'] ?? 'Tanaman Belum Diatur',
            'sensor_data' => $device['sensor_data'] ?? [],
            'control_status' => $device['control_status'] ?? [],
            'wifi_config' => $device['wifi_config'] ?? [],
            'history' => $device['history'] ?? [],
            'last_seen' => $lastSeen,
            'connection_status' => now()->timestamp - $lastSeen <= 90 ? 'online' : 'offline',
        ];
    }

    public function updatePlantName(string $deviceId, string $plantName): array
    {
        return $this->firebase->updateDevice($deviceId, [
            'plant_name' => $plantName,
            'updated_at' => now()->timestamp,
        ]);
    }

    public function updateSchedule(string $deviceId, string $time): array
    {
        return $this->firebase->updateDevice($deviceId, [
            'control_status' => [
                'manual_schedule' => [
                    'time' => $time,
                    'enabled' => true,
                    'updated_at' => now()->timestamp,
                ],
            ],
        ]);
    }

    public function updateWifiConfig(string $deviceId, string $ssid, string $password): array
    {
        return $this->firebase->updateDevice($deviceId, [
            'wifi_config' => [
                'ssid' => $ssid,
                'password' => $password,
                'updated_at' => now()->timestamp,
            ],
        ]);
    }

    public function triggerManualPump(string $deviceId, int $durationSeconds): array
    {
        $duration = max(1, min($durationSeconds, 60));

        return $this->firebase->updateDevice($deviceId, [
            'control_status' => [
                'manual_trigger' => [
                    'active' => true,
                    'duration_sec' => $duration,
                    'requested_at' => now()->timestamp,
                ],
            ],
        ]);
    }

    public function historyCollection(string $deviceId): array
    {
        $history = $this->firebase->getHistory($deviceId);
        $rows = [];

        foreach ($history as $key => $item) {
            $rows[] = [
                'log_id' => $key,
                'mode' => $item['mode'] ?? '-',
                'started_at' => Carbon::createFromTimestamp((int) ($item['started_at'] ?? 0))->toDateTimeString(),
                'duration_sec' => $item['duration_sec'] ?? 0,
                'soil_before' => $item['soil_before'] ?? null,
                'soil_after' => $item['soil_after'] ?? null,
            ];
        }

        return $rows;
    }
}
