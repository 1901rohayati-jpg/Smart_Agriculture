<?php

namespace App\Http\Controllers;

use App\Exports\WateringHistoryExport;
use App\Services\DeviceService;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class HistoryExportController extends Controller
{
    public function __construct(private readonly DeviceService $deviceService)
    {
    }

    public function export(): BinaryFileResponse
    {
        $rows = $this->deviceService->historyCollection(session('device_id'));

        return Excel::download(new WateringHistoryExport($rows), 'watering-history.xlsx');
    }
}
