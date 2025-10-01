<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Service extends Model
{
    /** @use HasFactory<\Database\Factories\ServiceFactory> */
    use HasFactory;
    
    protected $fillable = [
        'nom',
        'description',
        'avantage',
        'delai',
        'montant_min',
        'document_requis',
        'user_id'
    ];

    public function demandes(): HasMany
    {
        return $this->hasMany(Demande::class, 'service_id');
    }
}
