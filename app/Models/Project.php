<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id',
        'contact_id',
        'assigned_pm_id',
        'name',
        'methodology',
        'status',
    ];
    public function generateTasksFromTemplate(): void
    {
        // منع التوليد المتكرر - إذا المشروع عنده مهام أصلاً، لا تولّد مرة تانية
        if ($this->tasks()->exists()) {
            return;
        }

        $templates = WorkflowSetting::where('methodology', $this->methodology)
            ->orderBy('sequence')
            ->get();

        foreach ($templates as $template) {
            $this->tasks()->create([
                'title' => $template->task_name,
                'allocation' => $template->allocation,
                'days' => $template->days,
                'note' => $template->note,
                'related' => $template->related,
                'sequence' => $template->sequence,
                'status' => 'pending',
                'required_role' => $template->required_role,
            ]);
        }
    }
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function projectManager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_pm_id');
    }

    public function requirements(): HasMany
    {
        return $this->hasMany(ProjectRequirement::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class)->orderBy('sequence');
    }

    public function allTasksCompleted(): bool
    {
        return $this->tasks()->count() > 0
            && $this->tasks()->where('status', '!=', 'completed')->doesntExist();
    }

    public function refreshProjectStatus(): void
    {
        if ($this->allTasksCompleted() && $this->status !== 'Completed') {
            $this->update(['status' => 'Completed']);
        }
    }
}
