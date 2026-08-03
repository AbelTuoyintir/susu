<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

class Setting extends Model
{
    use BelongsToTenant;

    protected $fillable = ['key', 'value', 'description', 'tenant_id'];

    /**
     * Quick helper to get or default a setting
     */
    public static function val($key, $default = null)
    {
        $tenantId = null;
        if (auth()->check() && auth()->user()->tenant_id) {
            $tenantId = auth()->user()->tenant_id;
        } elseif (Tenant::currentId()) {
            $tenantId = Tenant::currentId();
        }

        if ($tenantId) {
            $setting = self::withoutGlobalScope('tenant')
                ->where('key', $key)
                ->where('tenant_id', $tenantId)
                ->first();
            if ($setting) {
                return $setting->value;
            }
        }

        // Fallback to global setting (tenant_id IS NULL)
        $setting = self::withoutGlobalScope('tenant')
            ->where('key', $key)
            ->whereNull('tenant_id')
            ->first();

        return $setting ? $setting->value : $default;
    }
}
