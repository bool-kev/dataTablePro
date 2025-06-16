<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Workspace extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'name',
        'slug',
        'description',
        'owner_id',
        'is_active',
        'last_accessed_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_accessed_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($workspace) {
            if (empty($workspace->slug)) {
                $workspace->slug = Str::slug($workspace->name) . '-' . Str::random(6);
            }
        });
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot('role', 'joined_at')
            ->withTimestamps();
    }

    public function importHistories(): HasMany
    {
        return $this->hasMany(ImportHistory::class);
    }

    public function importedData(): HasMany
    {
        return $this->hasMany(ImportedData::class)->through('importHistories');
    }

    public function canUserAccess(User $user, string $permission = 'view'): bool
    {
        if ($this->owner_id === $user->id) {
            return true;
        }
        
        $pivot = $this->users()->where('user_id', $user->id)->first();
        
        if (!$pivot) {
            return false;
        }
        
        $role = $pivot->pivot->role;
        
        return match($permission) {
            'view' => in_array($role, ['owner', 'admin', 'editor', 'viewer']),
            'edit' => in_array($role, ['owner', 'admin', 'editor']),
            'admin' => in_array($role, ['owner', 'admin']),
            'owner' => $role === 'owner',
            default => false,
        };
    }
}
