<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class WorkflowSetting extends Model
{
    use HasFactory;

    public const METHODOLOGY_QUANTITATIVE = 'Quantitative';
    public const METHODOLOGY_QUALITATIVE = 'Qualitative';

    public const METHODOLOGIES = [
        self::METHODOLOGY_QUANTITATIVE,
        self::METHODOLOGY_QUALITATIVE,
    ];

    protected $fillable = [
        'methodology',
        'task_name',
        'allocation',
        'days',
        'note',
        'related',
        'sequence',
    ];

    protected $casts = [
        'sequence' => 'integer',
    ];

    public function scopeForMethodology(Builder $query, string $methodology): Builder
    {
        return $query->where('methodology', $methodology)->orderBy('sequence');
    }
}