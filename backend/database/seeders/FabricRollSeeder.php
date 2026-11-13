<?php

namespace Database\Seeders;

use App\Models\FabricRoll;
use App\Services\FabricShrinkageService;
use Illuminate\Database\Seeder;

class FabricRollSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $service = new FabricShrinkageService();

        $rolls = [
            // 1. Group 23 Worked Example
            [
                'roll_number' => 'R-2026-G23',
                'lot_number' => 'LOT-DNM-881',
                'fabric_type' => 'Denim Cotton 12oz',
                'buyer' => "Levi's",
                'order_number' => 'PO-98432',
                'stage' => 'Washed',
                'roll_length' => 120.50,
                'test_date' => '2026-11-10',
                'tested_by' => 'Senior Textile Lab Tech',
                'length_reading_1' => -3.50,
                'length_reading_2' => -3.40,
                'length_reading_3' => -3.60,
                'width_reading_1' => -2.50,
                'width_reading_2' => -2.40,
                'width_reading_3' => -2.60,
                'notes' => 'Standard Worked Example target: should match Group 23',
            ],

            // 2. Exact Boundary Matching for Group 23 (upper boundary: length -3.00, width -2.00)
            [
                'roll_number' => 'R-2026-BOUND-01',
                'lot_number' => 'LOT-DNM-882',
                'fabric_type' => 'Denim Cotton 12oz',
                'buyer' => "Levi's",
                'order_number' => 'PO-98432',
                'stage' => 'Washed',
                'roll_length' => 105.00,
                'test_date' => '2026-11-11',
                'tested_by' => 'QA Officer',
                'length_reading_1' => -3.00,
                'length_reading_2' => -3.00,
                'length_reading_3' => -3.00,
                'width_reading_1' => -2.00,
                'width_reading_2' => -2.00,
                'width_reading_3' => -2.00,
                'notes' => 'Exact upper boundary test: length -3.00%, width -2.00% (matches Group 23 upper limit)',
            ],

            // 3. Exact Lower Boundary Matching for Group 23 (length -4.00, width -3.00)
            [
                'roll_number' => 'R-2026-BOUND-02',
                'lot_number' => 'LOT-DNM-883',
                'fabric_type' => 'Denim Cotton 12oz',
                'buyer' => "Levi's",
                'order_number' => 'PO-98432',
                'stage' => 'Washed',
                'roll_length' => 110.00,
                'test_date' => '2026-11-11',
                'tested_by' => 'QA Officer',
                'length_reading_1' => -4.00,
                'length_reading_2' => -4.00,
                'length_reading_3' => -4.00,
                'width_reading_1' => -3.00,
                'width_reading_2' => -3.00,
                'width_reading_3' => -3.00,
                'notes' => 'Exact lower boundary test: length -4.00%, width -3.00% (matches Group 23 lower limit)',
            ],

            // 4. No Matching Group (out of all chart specifications)
            [
                'roll_number' => 'R-2026-NOMATCH-01',
                'lot_number' => 'LOT-EXP-999',
                'fabric_type' => 'Heavy Canvas',
                'buyer' => 'Carhartt',
                'order_number' => 'PO-44120',
                'stage' => 'Washed',
                'roll_length' => 80.00,
                'test_date' => '2026-11-12',
                'tested_by' => 'Senior Lab Tech',
                'length_reading_1' => -15.00,
                'length_reading_2' => -15.50,
                'length_reading_3' => -14.50,
                'width_reading_1' => -11.00,
                'width_reading_2' => -10.80,
                'width_reading_3' => -11.20,
                'notes' => 'Excessive shrinkage: should trigger NO_MATCH status',
            ],

            // 5. Multiple Matching Groups (falls into overlap of Group 99-A and Group 99-B)
            [
                'roll_number' => 'R-2026-MULTI-01',
                'lot_number' => 'LOT-TWL-501',
                'fabric_type' => 'Cotton Twill',
                'buyer' => 'Zara',
                'order_number' => 'PO-77812',
                'stage' => 'Washed',
                'roll_length' => 95.00,
                'test_date' => '2026-11-12',
                'tested_by' => 'Lab Analyst',
                'length_reading_1' => -2.20,
                'length_reading_2' => -2.20,
                'length_reading_3' => -2.20,
                'width_reading_1' => -1.20,
                'width_reading_2' => -1.20,
                'width_reading_3' => -1.20,
                'notes' => 'Falls in overlap range: matches Group 99-A and Group 99-B',
            ],
        ];

        // Add 15 additional realistic production rolls
        $stages = ['Washed', 'Finished', 'Greige'];
        $buyers = ["H&M", "Zara", "GAP", "Target", "Marks & Spencer", "Uniqlo"];
        $fabricTypes = ['100% Cotton Twill', 'Single Jersey', 'French Terry', 'Chino Poplin', 'CVC Pique'];

        for ($i = 1; $i <= 18; $i++) {
            $stage = $stages[$i % count($stages)];
            $buyer = $buyers[$i % count($buyers)];
            $fabric = $fabricTypes[$i % count($fabricTypes)];

            $lBase = -1.50 - (($i % 8) * 0.45);
            $wBase = -1.00 - (($i % 6) * 0.35);

            $rolls[] = [
                'roll_number' => sprintf('R-2026-PRD-%03d', $i),
                'lot_number' => sprintf('LOT-PRD-%03d', 100 + $i),
                'fabric_type' => $fabric,
                'buyer' => $buyer,
                'order_number' => sprintf('PO-%d', 55000 + $i * 12),
                'stage' => $stage,
                'roll_length' => round(75.0 + ($i * 4.5), 1),
                'test_date' => date('Y-m-d', strtotime("-{$i} days")),
                'tested_by' => 'Quality Inspector ' . (($i % 3) + 1),
                'length_reading_1' => round($lBase + 0.15, 2),
                'length_reading_2' => round($lBase - 0.10, 2),
                'length_reading_3' => round($lBase - 0.05, 2),
                'width_reading_1' => round($wBase + 0.10, 2),
                'width_reading_2' => round($wBase - 0.15, 2),
                'width_reading_3' => round($wBase + 0.05, 2),
                'notes' => "Production batch {$i} inspection",
            ];
        }

        foreach ($rolls as $rollData) {
            $existing = FabricRoll::where('roll_number', $rollData['roll_number'])->first();
            if ($existing) {
                $existing->fill($rollData);
                $service->classifyRoll($existing, true);
            } else {
                $roll = new FabricRoll($rollData);
                $service->classifyRoll($roll, true);
            }
        }
    }
}
