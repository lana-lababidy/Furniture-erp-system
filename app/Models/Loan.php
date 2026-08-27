<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Loan extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'total_amount',
        'monthly_installment',
        'remaining_amount',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'total_amount' => 'decimal:2',
            'monthly_installment' => 'decimal:2',
            'remaining_amount' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * القسط الفعلي الذي يُقتطع هذا الشهر (لا يتجاوز المبلغ المتبقي).
     */
    public function effectiveInstallment(): float
    {
        return (float) min($this->monthly_installment, $this->remaining_amount);
    }
}
