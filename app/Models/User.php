<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/*
|--------------------------------------------------------------------------
| JWT Interface
|--------------------------------------------------------------------------
| This interface is required by jwt-auth package.
| It tells Laravel how to identify and create JWT tokens for the user.
*/
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /*
    |--------------------------------------------------------------------------
    | Mass Assignable Attributes
    |--------------------------------------------------------------------------
    | These fields can be filled using User::create()
    */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /*
    |--------------------------------------------------------------------------
    | Hidden Attributes
    |--------------------------------------------------------------------------
    | These attributes will be hidden when returning user as JSON
    */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /*
    |--------------------------------------------------------------------------
    | Attribute Casting
    |--------------------------------------------------------------------------
    | Laravel 12 uses method-based casting
    */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed', // Automatically hashes password
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | JWT Required Methods
    |--------------------------------------------------------------------------
    | These two methods are mandatory for JWT authentication
    */

    /**
     * Get the identifier that will be stored in the JWT.
     * Usually the user's primary key (id).
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return custom claims for JWT.
     * We are not adding any extra claims now.
     */
    public function getJWTCustomClaims()
    {
        return [];
    }
}
