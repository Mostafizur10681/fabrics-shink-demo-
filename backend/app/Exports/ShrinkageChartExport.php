<?php

namespace App\Exports;

use App\Models\ShrinkageChart;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ShrinkageChartExport implements FromCollection, WithHeadings, WithMapping
{
    protected ?string $stage;

    public function __construct(?string $stage = null)
    {
        $this->stage = $stage;
    }

    public function collection()
    {
        $query = ShrinkageChart::query();
        if ($this->stage && $this->stage !== 'All') {
            $query->where('stage', $this->stage);
        }
        return $query->orderBy('id', 'asc')->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Group Name',
            'Group Code',
            'Stage',
            'Fabric Type',
            'Length Min (%)',
            'Length Max (%)',
            'Width Min (%)',
            'Width Max (%)',
            'Tolerance (%)',
            'Pattern Code',
            'Status',
            'Notes',
        ];
    }

    /**
     * @param ShrinkageChart $chart
     */
    public function map($chart): array
    {
        return [
            $chart->id,
            $chart->group_name,
            $chart->group_code,
            $chart->stage,
            $chart->fabric_type,
            $chart->length_min,
            $chart->length_max,
            $chart->width_min,
            $chart->width_max,
            $chart->tolerance,
            $chart->pattern_code,
            $chart->is_active ? 'Active' : 'Inactive',
            $chart->notes,
        ];
    }
}

