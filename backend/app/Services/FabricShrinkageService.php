<?php

namespace App\Services;

use App\Models\FabricRoll;
use App\Models\ShrinkageChart;
use Carbon\Carbon;

class FabricShrinkageService
{
    /**
     * Calculate 6-reading shrinkage averages.
     */
    public function calculateAverages(float $l1, float $l2, float $l3, float $w1, float $w2, float $w3): array
    {
        $lengthAvg = round(($l1 + $l2 + $l3) / 3, 2);
        $widthAvg = round(($w1 + $w2 + $w3) / 3, 2);

        return [
            'length_avg' => $lengthAvg,
            'width_avg' => $widthAvg,
        ];
    }

    /**
     * Classify fabric roll averages against the shrinkage charts.
     */
    public function classify(float $lengthAvg, float $widthAvg, ?string $stage = null, ?string $fabricType = null): array
    {
        $query = ShrinkageChart::where('is_active', true);

        if ($stage && $stage !== 'All') {
            $query->where(function ($q) use ($stage) {
                $q->where('stage', $stage)
                  ->orWhere('stage', 'All');
            });
        }

        $rules = $query->get();

        $matchingRules = [];
        $boundaryHit = false;

        foreach ($rules as $rule) {
            $lMin = min((float)$rule->length_min, (float)$rule->length_max);
            $lMax = max((float)$rule->length_min, (float)$rule->length_max);
            $wMin = min((float)$rule->width_min, (float)$rule->width_max);
            $wMax = max((float)$rule->width_min, (float)$rule->width_max);

            $tolerance = (float)($rule->tolerance ?? 0.0);
            $lMin -= $tolerance;
            $lMax += $tolerance;
            $wMin -= $tolerance;
            $wMax += $tolerance;

            // Check if averages fall within bounds (inclusive)
            $lMatches = ($lengthAvg >= ($lMin - 0.0001)) && ($lengthAvg <= ($lMax + 0.0001));
            $wMatches = ($widthAvg >= ($wMin - 0.0001)) && ($widthAvg <= ($wMax + 0.0001));

            if ($lMatches && $wMatches) {
                $isExactBoundary = (
                    abs($lengthAvg - $lMin) < 0.001 ||
                    abs($lengthAvg - $lMax) < 0.001 ||
                    abs($widthAvg - $wMin) < 0.001 ||
                    abs($widthAvg - $wMax) < 0.001
                );

                if ($isExactBoundary) {
                    $boundaryHit = true;
                }

                $matchingRules[] = [
                    'rule' => $rule,
                    'is_boundary' => $isExactBoundary,
                    'l_min' => $lMin,
                    'l_max' => $lMax,
                    'w_min' => $wMin,
                    'w_max' => $wMax,
                ];
            }
        }

        $matchCount = count($matchingRules);

        if ($matchCount === 0) {
            return [
                'status' => 'NO_MATCH',
                'matched_group' => null,
                'matched_chart_id' => null,
                'is_boundary_match' => false,
                'matching_details' => [
                    'message' => 'No matching shrinkage group found within specifications for the selected stage.',
                    'length_avg' => $lengthAvg,
                    'width_avg' => $widthAvg,
                    'stage' => $stage,
                ],
            ];
        }

        if ($matchCount === 1) {
            $primary = $matchingRules[0];
            $rule = $primary['rule'];

            return [
                'status' => 'MATCHED',
                'matched_group' => $rule->group_name,
                'matched_chart_id' => $rule->id,
                'is_boundary_match' => $primary['is_boundary'],
                'matching_details' => [
                    'group_name' => $rule->group_name,
                    'group_code' => $rule->group_code,
                    'stage' => $rule->stage,
                    'pattern_code' => $rule->pattern_code,
                    'length_range' => "[{$primary['l_min']}%, {$primary['l_max']}%]",
                    'width_range' => "[{$primary['w_min']}%, {$primary['w_max']}%]",
                    'is_exact_boundary' => $primary['is_boundary'],
                ],
            ];
        }

        // Multiple matching groups
        $candidateNames = array_map(fn($item) => $item['rule']->group_name, $matchingRules);
        $candidateDetails = array_map(function ($item) {
            return [
                'id' => $item['rule']->id,
                'group_name' => $item['rule']->group_name,
                'group_code' => $item['rule']->group_code,
                'stage' => $item['rule']->stage,
                'pattern_code' => $item['rule']->pattern_code,
                'length_range' => "[{$item['l_min']}%, {$item['l_max']}%]",
                'width_range' => "[{$item['w_min']}%, {$item['w_max']}%]",
                'is_boundary' => $item['is_boundary'],
            ];
        }, $matchingRules);

        return [
            'status' => 'MULTIPLE_MATCHES',
            'matched_group' => implode(', ', $candidateNames),
            'matched_chart_id' => null,
            'is_boundary_match' => $boundaryHit,
            'matching_details' => [
                'message' => 'Multiple matching shrinkage groups detected. Please review overlap tolerances.',
                'candidates' => $candidateDetails,
                'length_avg' => $lengthAvg,
                'width_avg' => $widthAvg,
                'stage' => $stage,
            ],
        ];
    }

    /**
     * Process and classify a FabricRoll model.
     */
    public function classifyRoll(FabricRoll $roll, bool $save = true): FabricRoll
    {
        // Re-calculate averages to ensure consistency
        $averages = $this->calculateAverages(
            (float)$roll->length_reading_1,
            (float)$roll->length_reading_2,
            (float)$roll->length_reading_3,
            (float)$roll->width_reading_1,
            (float)$roll->width_reading_2,
            (float)$roll->width_reading_3
        );

        $roll->length_shrinkage_avg = $averages['length_avg'];
        $roll->width_shrinkage_avg = $averages['width_avg'];

        $result = $this->classify(
            $roll->length_shrinkage_avg,
            $roll->width_shrinkage_avg,
            $roll->stage,
            $roll->fabric_type
        );

        $roll->classification_status = $result['status'];
        $roll->matched_group = $result['matched_group'];
        $roll->matched_chart_id = $result['matched_chart_id'];
        $roll->is_boundary_match = $result['is_boundary_match'];
        $roll->matching_details = $result['matching_details'];
        $roll->classified_at = Carbon::now();

        if ($save) {
            $roll->save();
        }

        return $roll;
    }

    /**
     * Reclassify all fabric rolls in batch.
     */
    public function classifyAll(?string $stage = null): int
    {
        $query = FabricRoll::query();
        if ($stage && $stage !== 'All') {
            $query->where('stage', $stage);
        }

        $rolls = $query->get();
        $count = 0;
        foreach ($rolls as $roll) {
            $this->classifyRoll($roll, true);
            $count++;
        }

        return $count;
    }
}

