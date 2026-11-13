<?php

namespace App\Exports;

use App\Models\FabricRoll;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class FabricRollExport implements FromCollection, WithHeadings, WithMapping
{
    protected ?string $stage;
    protected ?string $status;

    public function __construct(?string $stage = null, ?string $status = null)
    {
        $this->stage = $stage;
        $this->status = $status;
    }

    public function collection()
    {
        $query = FabricRoll::query();
        if ($this->stage && $this->stage !== 'All') {
            $query->where('stage', $this->stage);
        }
        if ($this->status && $this->status !== 'All') {
            $query->where('classification_status', $this->status);
        }
        return $query->orderBy('test_date', 'desc')->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Roll Number',
            'Lot Number',
            'Fabric Type',
            'Buyer',
            'Order Number',
            'Stage',
            'Roll Length (m)',
            'Test Date',
            'Length R1 (%)',
            'Length R2 (%)',
            'Length R3 (%)',
            'Length Avg (%)',
            'Width R1 (%)',
            'Width R2 (%)',
            'Width R3 (%)',
            'Width Avg (%)',
            'Matched Group',
            'Classification Status',
            'Boundary Match',
            'Tested By',
            'Notes',
        ];
    }

    /**
     * @param FabricRoll $roll
     */
    public function map($roll): array
    {
        return [
            $roll->id,
            $roll->roll_number,
            $roll->lot_number,
            $roll->fabric_type,
            $roll->buyer,
            $roll->order_number,
            $roll->stage,
            $roll->roll_length,
            $roll->test_date ? $roll->test_date->format('Y-m-d') : '',
            $roll->length_reading_1,
            $roll->length_reading_2,
            $roll->length_reading_3,
            $roll->length_shrinkage_avg,
            $roll->width_reading_1,
            $roll->width_reading_2,
            $roll->width_reading_3,
            $roll->width_shrinkage_avg,
            $roll->matched_group ?? 'N/A',
            $roll->classification_status,
            $roll->is_boundary_match ? 'Yes' : 'No',
            $roll->tested_by,
            $roll->notes,
        ];
    }
}

