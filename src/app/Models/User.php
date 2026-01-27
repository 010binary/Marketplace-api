<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    protected $fillable = ["name", "email", "password", "role"];

    protected $hidden = ["password", "remember_token"];

    protected function casts(): array
    {
        return [
            "email_verified_at" => "datetime",
            "password" => "hashed",
        ];
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, "creator_id");
    }

    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class);
    }

    public function isCreator(): bool
    {
        return $this->role === "creator";
    }

    public function isCustomer(): bool
    {
        return $this->role === "customer";
    }

    public function isAdmin(): bool
    {
        return $this->role === "admin";
    }
}
