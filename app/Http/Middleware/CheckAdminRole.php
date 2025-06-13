<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckAdminRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Using the existing auth system (which is already checking the token)
        $userId = $request->authenticated_user_id ?? Auth::id();
        
        if (!$userId) {
            return response()->json([
                'message' => 'Unauthorized access'
            ], Response::HTTP_UNAUTHORIZED);
        }
        
        // Check if user has admin role (assuming role field exists in users table)
        // If not, we can check a specific 'is_admin' field or any other admin identification method
        $user = Auth::user();
        
        if (!$user || $user->role !== 'admin') {
            return response()->json([
                'message' => 'Access denied. Admin permissions required.'
            ], Response::HTTP_FORBIDDEN);
        }
        
        return $next($request);
    }
}
