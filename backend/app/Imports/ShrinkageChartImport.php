<?php

namespace App\Imports;

use App\Models\ShrinkageChart;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;

class ShrinkageChartImport implements ToModel, WithHeadingRow, SkipsEmptyRows
{
    private int $rowsImported = 0;

    public function model(array $row): ?ShrinkageChart
    {
        // Support flexible column names (with lowercase or underscore or spaces)
        $groupName = $row['group_name'] ?? $row['group'] ?? $row['group_number'] ?? null;
        if (!$groupName) {
            return null;
        }

        $lengthMin = isset($row['length_min']) ? (float)$row['length_min'] : (isset($row['min_length']) ? (float)$row['min_length'] : 0.0);
        $lengthMax = isset($row['length_max']) ? (float)$row['length_max'] : (isset($row['max_length']) ? (float)$row['max_length'] : 0.0);
        $widthMin = isset($row['width_min']) ? (float)$row['width_min'] : (isset($row['min_width']) ? (float)$row['min_width'] : 0.0);
        $widthMax = isset($row['width_max']) ? (float)$row['width_max'] : (isset($row['max_width']) ? (float)$row['max_width'] : 0.0);

        $this->rowsImported++;

        return ShrinkageChart::updateOrCreate(
            [
                'group_name' => trim((string)$groupName),
                'stage' => trim((string)($row['stage'] ?? 'Washed')),
            ],
            [
                'group_code' => $row['group_code'] ?? null,
                'fabric_type' => $row['fabric_type'] ?? 'All',
                'length_min' => $lengthMin,
                'length_max' => $lengthMax,
                'width_min' => $widthMin,
                'width_max' => $widthMax,
                'tolerance' => (float)($row['tolerance'] ?? 0.0),
                'pattern_code' => $row['pattern_code'] ?? null,
                'notes' => $row['notes'] ?? null,
                'is_active' => true,
            ]
        );
    }

    public function getRowCount(): int
    {
        return $this->rowsImported;
    }
}

