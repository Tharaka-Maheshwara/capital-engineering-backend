<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CostEstimation extends Model
{
    use HasFactory;

    protected $table = 'cost_estimations';

    protected $fillable = [
        'name',
        'phone',
        'email',
        'project_type',
        'sqft',
        'budget_type',
        'soil',
        'design',
        'stories',
        'roof',
        'base_cost',
        'total_cost',
    ];

    protected $casts = [
        'sqft' => 'integer',
        'base_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
    ];
}
