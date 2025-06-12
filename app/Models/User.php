<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, SoftDeletes, HasApiTokens, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'national_id',
        'country',
        'city',
        'district',
        'currency',
        'phone',
        'date_of_birth',
        'gender',
        'address',
        'postal_code',
        'two_factor_code',
        'two_factor_expires_at',
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
            'password' => 'hashed',
            'date_of_birth' => 'date',
            'two_factor_expires_at' => 'datetime',
        ];
    }

    public function generateTwoFactorCode(): string
    {
        $code = rand(100000, 999999);

        $this->update([
            'two_factor_code' => $code,
            'two_factor_expires_at' => Carbon::now()->addMinutes(5),
        ]);

        return $code;
    }

    public function verifyTwoFactorCode(string $code): bool
    {
        if (
            $this->two_factor_code === $code &&
            $this->two_factor_expires_at &&
            Carbon::now()->isBefore($this->two_factor_expires_at)
        ) {
            $this->update([
                'two_factor_code' => null,
                'two_factor_expires_at' => null,
            ]);
            return true;
        }

        return false;
    }
}
