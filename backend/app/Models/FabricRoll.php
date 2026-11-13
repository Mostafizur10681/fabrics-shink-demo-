<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FabricRoll extends Model
{
    use HasFactory;

    protected $fillable = [
        'roll_number',
        'lot_number',
        'fabric_type',
        'buyer',
        'order_number',
        'stage',
        'roll_length',
        'test_date',
        'tested_by',
        'length_reading_1',
        'length_reading_2',
        'length_reading_3',
        'width_reading_1',
        'width_reading_2',
        'width_reading_3',
        'length_shrinkage_avg',
        'width_shrinkage_avg',
        'matched_group',
        'matched_chart_id',
        'classification_status',
        'is_boundary_match',
        'matching_details',
        'classified_at',
        'notes',
    ];

    protected $casts = [
        'test_date' => 'date',
        'length_reading_1' => 'float',
        'length_reading_2' => 'float',
        'length_reading_3' => 'float',
        'width_reading_1' => 'float',
        'width_reading_2' => 'float',
        'width_reading_3' => 'float',
        'length_shrinkage_avg' => 'float',
        'width_shrinkage_avg' => 'float',
        'roll_length' => 'float',
        'is_boundary_match' => 'boolean',
        'matching_details' => 'array',
        'classified_at' => 'datetime',
    ];

    public function matchedChart(): BelongsTo
    {
        return $this->belongsTo(ShrinkageChart::class, 'matched_chart_id');
    }
}

