<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tenant extends Model
{
    protected $fillable = ['name', 'slug', 'plan', 'status'];

    protected static $currentTenantId = null;

    public static $tierLimits = [
        'free' => [
            'users' => 10,
            'books' => 10,
            'loans' => 5,
        ],
        'premium' => [
            'users' => 100,
            'books' => 200,
            'loans' => 100,
        ],
        'enterprise' => [
            'users' => 999999,
            'books' => 999999,
            'loans' => 999999,
        ],
    ];

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

    public function getLimit($feature)
    {
        $plan = strtolower($this->plan ?? 'free');
        $limits = self::$tierLimits[$plan] ?? self::$tierLimits['free'];
        return $limits[$feature] ?? 0;
    }

    public function getUsage($feature)
    {
        if ($feature === 'users') {
            return User::withoutGlobalScope('tenant')->where('tenant_id', $this->id)->count();
        }
        if ($feature === 'books') {
            return Book::withoutGlobalScope('tenant')->where('tenant_id', $this->id)->count();
        }
        if ($feature === 'loans') {
            return Loan::withoutGlobalScope('tenant')->where('tenant_id', $this->id)->count();
        }
        return 0;
    }

    public function hasReachedLimit($feature)
    {
        return $this->getUsage($feature) >= $this->getLimit($feature);
    }
}
