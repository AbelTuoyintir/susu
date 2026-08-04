<?php

namespace App\Traits;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Builder;

trait BelongsToTenant
{
    /**
     * Boot the trait.
     */
    public static function bootBelongsToTenant()
    {
        static::creating(function ($model) {
            $tenantId = Tenant::currentId();
            if (! $tenantId && auth()->hasUser()) {
                $tenantId = auth()->user()->tenant_id;
            }

            if ($tenantId && ! $model->tenant_id) {
                $model->tenant_id = $tenantId;
            }
        });

        static::addGlobalScope('tenant', function (Builder $builder) {
            $tenantId = Tenant::currentId();
            if (! $tenantId && auth()->hasUser()) {
                $tenantId = auth()->user()->tenant_id;
            }

            if ($tenantId) {
                $builder->where($builder->getQuery()->from . '.tenant_id', $tenantId);
            }
        });
    }

    /**
     * Relationship with Tenant.
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
