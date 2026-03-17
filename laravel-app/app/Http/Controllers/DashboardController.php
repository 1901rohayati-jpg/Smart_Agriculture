<?php

namespace App\Http\Controllers;

use App\Services\DeviceService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(private readonly DeviceService $deviceService)
    {
    }

    public function index(): View
    {
        $dashboard = $this->deviceService->getDashboard(session('device_id'));

        return view('dashboard.index', compact('dashboard'));
    }
}
