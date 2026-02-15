<?php

namespace App\Http\Middleware;

use App\Models\AccessCodeLog;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LogAccessCodeActivity
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $accessCodeId = $request->session()->get('access_code_id');

        if (! $accessCodeId || ! $request->isMethod('GET')) {
            return $response;
        }

        $action = null;
        $metadata = null;

        $path = $request->path();

        if (preg_match('#^api/products/(\d+)$#', $path, $matches)) {
            $action = 'viewed_product';
            $metadata = ['product_id' => (int) $matches[1]];
        } elseif ($path === 'api/products') {
            $action = 'viewed_products';
            $params = $request->only(['teams', 'competitions', 'search', 'date_from', 'date_to']);
            $metadata = array_filter($params) ?: null;
        }

        if ($action) {
            AccessCodeLog::create([
                'access_code_id' => $accessCodeId,
                'action' => $action,
                'metadata' => $metadata,
                'ip_address' => $request->ip(),
                'created_at' => now(),
            ]);
        }

        return $response;
    }
}
