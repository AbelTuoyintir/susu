<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tenant extends Model
{
    protected $fillable = ['name', 'slug'];

    protected static $currentTenantId = null;

    /**
     * Get the current tenant ID in execution context.
     */
    public static function currentId()
    {
        return self::$currentTenantId;
    }

    /**
     * Set the current tenant ID.
     */
    public static function setTenantId($id)
    {
        self::$currentTenantId = $id;
    }

    /**
     * Forget the current tenant ID.
     */
    public static function forgetTenantId()
    {
        self::$currentTenantId = null;
    }

    /**
     * Execute a callback under a given tenant context.
     */
    public static function forTenant($id, \Closure $callback)
    {
        $originalTenantId = self::$currentTenantId;
        self::setTenantId($id);
        try {
            return $callback();
        } finally {
            self::setTenantId($originalTenantId);
        }
    }

    /**
     * Relationship with users.
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }
}
