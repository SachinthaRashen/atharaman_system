<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name', 'email', 'password', 'role', 
        'phone_number', 'nic_passport_number', 'residential_address',
        'date_of_birth', 'country', 'prefers_guided_tours', 'requires_accessibility',
        'native_language', 'preferred_travel_style', 'preferred_budget_tier', 'ml_cluster_id'
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
            'prefers_guided_tours' => 'boolean',
            'requires_accessibility' => 'boolean',
        ];
    }

    // A user can submit multiple role requests over time
    public function roleRequests()
    {
        return $this->hasMany(RoleRequest::class);
    }

    // SME Vendor Portfolios
    public function guides() { return $this->hasOne(Guide::class); }
    public function hotels() { return $this->hasMany(Hotel::class); }
    public function vehicles() { return $this->hasMany(Vehicle::class); }
    public function shops() { return $this->hasMany(Shop::class); }

    // User Activity
    public function reviews() { return $this->hasMany(Review::class); }
    public function interactions() { return $this->hasMany(UserInteraction::class); }
    public function recommendationLogs() { return $this->hasMany(RecommendationLog::class); }
}
