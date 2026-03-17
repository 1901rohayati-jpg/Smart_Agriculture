<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginByDeviceRequest;
use App\Services\DeviceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function __construct(private readonly DeviceService $deviceService)
    {
    }

    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function login(LoginByDeviceRequest $request): RedirectResponse
    {
        $deviceId = $request->validated('device_id');

        if (!$this->deviceService->authenticateByDeviceId($deviceId)) {
            return back()->withErrors(['device_id' => 'Device ID tidak ditemukan.'])->withInput();
        }

        $request->session()->put('device_id', $deviceId);

        return redirect()->route('dashboard.index');
    }

    public function logout(): RedirectResponse
    {
        session()->forget('device_id');

        return redirect()->route('login.form');
    }
}
