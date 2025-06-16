<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class ImportedData extends Model
{
    protected $fillable = [
        'import_history_id',
        'data',
        'row_hash',
    ];

    protected $casts = [
        'data' => 'array',
    ];

    public function importHistory(): BelongsTo
    {
        return $this->belongsTo(ImportHistory::class);
    }

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class)->through('importHistory');
    }

    /**
     * Scope to filter by workspace
     */
    public function scopeForWorkspace(Builder $query, Workspace $workspace): Builder
    {
        return $query->whereHas('importHistory', function (Builder $q) use ($workspace) {
            $q->where('workspace_id', $workspace->id);
        });
    }

    public function getDataAttribute($value)
    {
        return json_decode($value, true);
    }

    public function setDataAttribute($value)
    {
        $this->attributes['data'] = json_encode($value);
        $this->attributes['row_hash'] = md5(json_encode($value));
    }
}
