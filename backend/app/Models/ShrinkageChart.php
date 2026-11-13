<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShrinkageChart extends Model
{
    use HasFactory;

    protected $fillable = [
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
        'is_active',
    ];

    protected $casts = [
        'length_min' => 'float',
        'length_max' => 'float',
        'width_min' => 'float',
        'width_max' => 'float',
        'tolerance' => 'float',
        'is_active' => 'boolean',
    ];

    public function fabricRolls(): HasMany
    {
        return $this->hasMany(FabricRoll::class, 'matched_chart_id');
    }
}

