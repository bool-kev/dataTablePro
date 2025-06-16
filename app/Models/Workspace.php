<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Workspace extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'database_name',
        'database_path',
        'database_type',
        'database_config',
        'owner_id',
        'is_active',
        'last_accessed_at',
    ];

    protected $casts = [
        'database_config' => 'array',
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
            
            if (empty($workspace->database_name)) {
                $workspace->database_name = 'workspace_' . $workspace->slug;
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

    public function getDatabaseConnectionName(): string
    {
        return 'workspace_' . $this->id;
    }

    public function getDatabasePath(): string
    {
        if ($this->database_type === 'sqlite') {
            return database_path('workspaces/' . $this->database_name . '.sqlite');
        }
        
        return $this->database_path;
    }

    public function createDatabase(): bool
    {
        if ($this->database_type === 'sqlite') {
            $path = $this->getDatabasePath();
            $directory = dirname($path);
            
            if (!file_exists($directory)) {
                mkdir($directory, 0755, true);
            }
            
            if (!file_exists($path)) {
                touch($path);
                $this->update(['database_path' => $path]);
                return true;
            }
        }
        
        return false;
    }

    public function setupDatabaseConnection(): void
    {
        $connectionName = $this->getDatabaseConnectionName();
        
        if ($this->database_type === 'sqlite') {
            config([
                "database.connections.{$connectionName}" => [
                    'driver' => 'sqlite',
                    'database' => $this->getDatabasePath(),
                    'prefix' => '',
                    'foreign_key_constraints' => true,
                ]
            ]);
        }
        
        // Purger le cache de connexion pour forcer la reconnexion
        app('db')->purge($connectionName);
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
