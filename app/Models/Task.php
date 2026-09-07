<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use SebastianBergmann\CodeCoverage\Report\Xml\Project;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'title',
        'allocation',
        'days',
        'note',
        'related',
        'sequence',
        'status',
        'started_at',
        'finished_at',
    ];

    protected function casts(): array
    {
        return [
            'sequence' => 'integer',
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * هل هذه المهمة مفتوحة للبدء؟ (كل المهام السابقة بالتسلسل مكتملة)
     */
    public function isUnlocked(): bool
    {
        return !$this->project
            ->tasks()
            ->where('sequence', '<', $this->sequence)
            ->where('status', '!=', 'completed')
            ->exists();
    }

    public function projectRequirements(): BelongsToMany
    {
        return $this->belongsToMany(ProjectRequirement::class, 'project_requirement_task')
            ->withTimestamps();
    }
}
