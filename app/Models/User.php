<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'nom',
        'prenom',
        'email',
        'telephone',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function particulier(): HasOne
    {
        return $this->hasOne(Particulier::class, 'user_id');
    }

    public function entreprise(): HasOne
    {
        return $this->hasOne(Entreprise::class, 'user_id');
    }

    public function institution(): HasOne
    {
        return $this->hasOne(Institution::class, 'user_id');
    }

    public function demandes(): HasMany
    {
        return $this->hasMany(Demande::class, 'user_id');
    }

    public function isAdmin(): bool
    {
        return $this->institution !== null;
    }

    public function profileType(): ?string
    {
        if ($this->particulier) {
            return 'particulier';
        }

        if ($this->entreprise) {
            return 'entreprise';
        }

        if ($this->institution) {
            return 'institution';
        }

        return null;
    }

    public function profileTypeLabel(): ?string
    {
        $type = $this->profileType();

        return $type ? match ($type) {
            'particulier' => 'Particulier',
            'entreprise' => 'Entreprise',
            'institution' => 'Institution',
            default => null,
        } : null;
    }

    public function ensureProfileType(?string $type): bool
    {
        if ($type === null) {
            return true;
        }

        return $this->profileType() === strtolower($type);
    }

    public function phoneVerificationCode(): HasOne
    {
        return $this->hasOne(PhoneVerificationCode::class);
    }
}
