<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Document extends Model
{
    /** @use HasFactory<\Database\Factories\DocumentFactory> */
    use HasFactory;

    protected $fillable = [
        'demande_id',
        'nom',
        'path',
        'user_id'
    ];
    public function demande(): BelongsTo
    {
        return $this->belongsTo(Demande::class, 'demande_id');
    }
}
