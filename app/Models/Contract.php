<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Contract extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'base_salary',
        'start_date',
        'end_date',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'base_salary' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * هل العقد يقترب من الانتهاء خلال عدد أيام معين (افتراضي 30 يوم).
     */
    public function isExpiringSoon(int $days = 30): bool
    {
        if (!$this->end_date || $this->status !== 'active') {
            return false;
        }

        return now()->diffInDays($this->end_date, false) <= $days
            && now()->diffInDays($this->end_date, false) >= 0;
    }
}
