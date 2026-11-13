<?php

namespace Database\Seeders;

use App\Models\ShrinkageChart;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ShrinkageChartSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(bool $returnCount = false)
    {
        $records = $this->generateRecords();

        DB::transaction(function () use ($records) {
            foreach (array_chunk($records, 100) as $chunk) {
                foreach ($chunk as $row) {
                    ShrinkageChart::updateOrCreate(
                        [
                            'group_name' => $row['group_name'],
                            'stage' => $row['stage'],
                        ],
                        $row
                    );
                }
            }
        });

        // Also generate the sample CSV file in storage/app
        $csvPath = storage_path('app/sample_shrinkage_chart_400_plus.csv');
        $this->generateCsvFile($csvPath);

        if ($returnCount) {
            return count($records);
        }
    }

    /**
     * Generate 420 structured shrinkage rules.
     */
    public function generateRecords(): array
    {
        $records = [];
        $now = now();

        // 1. Explicit Group 23 for Washed stage (Primary worked example)
        $records[] = [
            'group_name' => 'Group 23',
            'group_code' => 'GRP-023',
            'stage' => 'Washed',
            'fabric_type' => 'Denim Cotton',
            'length_min' => -4.00,
            'length_max' => -3.00,
            'width_min' => -3.00,
            'width_max' => -2.00,
            'tolerance' => 0.00,
            'pattern_code' => 'PTN-W23',
            'notes' => 'Expected benchmark Group 23 worked example (Washed)',
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ];

        // 2. Intentional overlapping groups for testing Multiple Matching Groups
        $records[] = [
            'group_name' => 'Group 99-A',
            'group_code' => 'GRP-099A',
            'stage' => 'Washed',
            'fabric_type' => 'Cotton Twill',
            'length_min' => -2.80,
            'length_max' => -2.00,
            'width_min' => -1.80,
            'width_max' => -1.00,
            'tolerance' => 0.00,
            'pattern_code' => 'PTN-99A',
            'notes' => 'Test overlap rule A for multiple matches verification',
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ];

        $records[] = [
            'group_name' => 'Group 99-B',
            'group_code' => 'GRP-099B',
            'stage' => 'Washed',
            'fabric_type' => 'Cotton Twill',
            'length_min' => -2.50,
            'length_max' => -1.80,
            'width_min' => -1.50,
            'width_max' => -0.80,
            'tolerance' => 0.00,
            'pattern_code' => 'PTN-99B',
            'notes' => 'Test overlap rule B for multiple matches verification',
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ];

        // 3. Systematically generate distinct non-colliding tiles across stages
        $stages = ['Washed', 'Finished', 'Greige'];
        $fabricTypes = ['Denim', '100% Cotton', 'CVC Knit', 'Single Jersey', 'Twill', 'Poplin', 'Rib Knit'];

        $ruleIndex = 1;
        foreach ($stages as $stageIndex => $stage) {
            // Generate a 12x12 grid per stage (144 per stage * 3 = 432 rules)
            for ($lBin = 0; $lBin < 12; $lBin++) {
                for ($wBin = 0; $wBin < 12; $wBin++) {
                    $groupNum = $ruleIndex;
                    $ruleIndex++;

                    // Skip Group 23 on Washed since it's defined explicitly above
                    if ($stage === 'Washed' && $groupNum === 23) {
                        continue;
                    }

                    // Map grid coordinates to negative shrinkage percentages
                    // Ensure Washed grid does not overlap with Group 23 [-4.00, -3.00] x [-3.00, -2.00]
                    // or with Group 99-A/B [-2.80, -1.80] x [-1.80, -0.80]
                    $lMin = -4.30 - ($lBin * 0.45);
                    $lMax = round($lMin + 0.25, 2);
                    $wMin = -3.30 - ($wBin * 0.40);
                    $wMax = round($wMin + 0.25, 2);

                    // For Finished and Greige stages, provide varied standard ranges
                    if ($stage === 'Finished') {
                        $lMin = -0.50 - ($lBin * 0.40);
                        $lMax = round($lMin + 0.35, 2);
                        $wMin = -0.30 - ($wBin * 0.35);
                        $wMax = round($wMin + 0.30, 2);
                    } elseif ($stage === 'Greige') {
                        $lMin = -5.00 - ($lBin * 0.50);
                        $lMax = round($lMin + 0.45, 2);
                        $wMin = -4.00 - ($wBin * 0.45);
                        $wMax = round($wMin + 0.40, 2);
                    }

                    $fabricType = $fabricTypes[($groupNum % count($fabricTypes))];

                    $records[] = [
                        'group_name' => "Group {$groupNum}",
                        'group_code' => sprintf('GRP-%03d', $groupNum),
                        'stage' => $stage,
                        'fabric_type' => $fabricType,
                        'length_min' => $lMin,
                        'length_max' => $lMax,
                        'width_min' => $wMin,
                        'width_max' => $wMax,
                        'tolerance' => 0.00,
                        'pattern_code' => sprintf('PTN-%s-%03d', substr($stage, 0, 1), $groupNum),
                        'notes' => "Standard shrinkage chart specification for {$stage} {$fabricType}",
                        'is_active' => true,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];

                    if (count($records) >= 420) {
                        break 3;
                    }
                }
            }
        }

        return $records;
    }

    /**
     * Generate a CSV file containing 400+ chart records.
     */
    public function generateCsvFile(string $path): void
    {
        $dir = dirname($path);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        $records = $this->generateRecords();
        $handle = fopen($path, 'w');

        // Header row
        fputcsv($handle, [
            'group_name',
            'group_code',
            'stage',
            'fabric_type',
            'length_min',
            'length_max',
            'width_min',
            'width_max',
            'tolerance',
            'pattern_code',
            'notes',
        ]);

        foreach ($records as $row) {
            fputcsv($handle, [
                $row['group_name'],
                $row['group_code'],
                $row['stage'],
                $row['fabric_type'],
                $row['length_min'],
                $row['length_max'],
                $row['width_min'],
                $row['width_max'],
                $row['tolerance'],
                $row['pattern_code'],
                $row['notes'],
            ]);
        }

        fclose($handle);
    }
}
