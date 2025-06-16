<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class ImportHistory extends Model
{
    protected $fillable = [
        'workspace_id',
        'filename',
        'original_filename',
        'file_path',
        'file_type',
        'total_rows',
        'successful_rows',
        'failed_rows',
        'errors',
        'status',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'errors' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function importedData(): HasMany
    {
        return $this->hasMany(ImportedData::class);
    }

    /**
     * Scope to filter by workspace
     */
    public function scopeForWorkspace(Builder $query, Workspace $workspace): Builder
    {
        return $query->where('workspace_id', $workspace->id);
    }

    public function getSuccessRateAttribute(): float
    {
        return $this->total_rows > 0 ? ($this->successful_rows / $this->total_rows) * 100 : 0;
    }
}
