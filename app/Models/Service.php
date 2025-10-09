<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use JsonException;

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
    protected function avantage(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->encodeJsonValue($value),
            set: fn ($value) => $this->prepareJsonForStorage($value)
        );
    }

    protected function documentRequis(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->encodeJsonValue($value),
            set: fn ($value) => $this->prepareJsonForStorage($value)
        );
    }

    private function encodeJsonValue(mixed $value): mixed
    {
        return is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : $value;
    }

    private function prepareJsonForStorage(mixed $value): mixed
    {
        if (is_array($value)) {
            return json_encode($value, JSON_UNESCAPED_UNICODE);
        }

        if (is_string($value)) {
            try {
                json_decode($value, true, flags: JSON_THROW_ON_ERROR);
            } catch (JsonException) {
                return $value;
            }

            return $value;
        }

        return $value;
    }

    public function demandes(): HasMany
    {
        return $this->hasMany(Demande::class, 'service_id');
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
