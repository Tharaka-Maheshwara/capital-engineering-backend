<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'api_token_hash',
        'api_token_expires_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'api_token_hash',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'api_token_expires_at' => 'datetime',
        ];
    }

    public function issueApiToken(int $expiresInMinutes = 10080): string
    {
        $token = Str::random(64);

        $this->forceFill([
            'api_token_hash' => hash('sha256', $token),
            'api_token_expires_at' => now()->addMinutes($expiresInMinutes),
        ])->save();

        return $token;
    }

    public function revokeApiToken(): void
    {
        $this->forceFill([
            'api_token_hash' => null,
            'api_token_expires_at' => null,
        ])->save();
    }
}