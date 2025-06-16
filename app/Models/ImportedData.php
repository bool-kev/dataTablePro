<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
