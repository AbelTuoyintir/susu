<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IdentifyTenant
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $host = $request->getHost();
        $parts = explode('.', $host);

        $tenant = null;
        if (count($parts) > 2 && $parts[0] !== 'www' && $parts[0] !== '127') {
            $slug = $parts[0];
            $tenant = Tenant::where('slug', $slug)->first();
        }

        if ($tenant) {
            Tenant::setTenantId($tenant->id);

            // Enforce suspension check
            if ($tenant->status === 'inactive') {
                if (auth()->check() && auth()->user()->role === 'super_admin') {
                    // Let super_admin bypass suspension
                } else {
                    abort(403, 'Your organization account is suspended. Please contact support.');
                }
            }
        } else {
            // Fallback: If logged in, set the tenant ID based on user
            if (auth()->check() && auth()->user()->tenant_id) {
                Tenant::setTenantId(auth()->user()->tenant_id);

                $tenant = auth()->user()->tenant;
                if ($tenant && $tenant->status === 'inactive') {
                    if (auth()->user()->role !== 'super_admin') {
                        abort(403, 'Your organization account is suspended. Please contact support.');
                    }
                }
            }
        }

        return $next($request);
    }
}
