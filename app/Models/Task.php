<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'role_id',
        'sequence',
        'status',
        'started_at',
        'finished_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function materials(): HasMany
    {
        return $this->hasMany(OrderMaterial::class);
    }

    public function isUnlocked(): bool
    {
        return !$this->order
            ->tasks()
            ->where('sequence', '<', $this->sequence)
            ->where('status', '!=', 'completed')
            ->exists();
    }
}