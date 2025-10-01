<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Particulier extends Model
{
    /** @use HasFactory<\Database\Factories\ParticulierFactory> */
    use HasFactory;

    protected $table = 'particuliers';
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function demandes(): HasMany
    {
        return $this->hasMany(Demande::class, 'user_id', 'user_id');
    }
}
