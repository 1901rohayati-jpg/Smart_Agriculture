<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class WateringHistoryExport implements FromCollection, WithHeadings
{
    public function __construct(private readonly array $rows)
    {
    }

    public function collection(): Collection
    {
        return collect($this->rows);
    }

    public function headings(): array
    {
        return ['Log ID', 'Mode', 'Started At', 'Duration (sec)', 'Soil Before', 'Soil After'];
    }
}
