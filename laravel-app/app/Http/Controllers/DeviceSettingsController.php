<?php

namespace App\Http\Controllers;

use App\Http\Requests\ManualTriggerRequest;
use App\Http\Requests\UpdatePlantNameRequest;
use App\Http\Requests\UpdateScheduleRequest;
use App\Http\Requests\UpdateWifiConfigRequest;
use App\Services\DeviceService;
use Illuminate\Http\RedirectResponse;

class DeviceSettingsController extends Controller
{
    public function __construct(private readonly DeviceService $deviceService)
    {
    }

    public function updatePlantName(UpdatePlantNameRequest $request): RedirectResponse
    {
        $this->deviceService->updatePlantName(session('device_id'), $request->validated('plant_name'));

        return back()->with('success', 'Nama tumbuhan berhasil diperbarui.');
    }

    public function updateSchedule(UpdateScheduleRequest $request): RedirectResponse
    {
        $this->deviceService->updateSchedule(session('device_id'), $request->validated('time'));

        return back()->with('success', 'Jadwal siram manual berhasil diperbarui.');
    }

    public function updateWifi(UpdateWifiConfigRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $this->deviceService->updateWifiConfig(session('device_id'), $data['ssid'], $data['password']);

        return back()->with('success', 'Konfigurasi WiFi berhasil dikirim.');
    }

    public function manualTrigger(ManualTriggerRequest $request): RedirectResponse
    {
        $this->deviceService->triggerManualPump(session('device_id'), (int) $request->validated('duration_sec'));

        return back()->with('success', 'Perintah manual pompa berhasil dikirim.');
    }
}
