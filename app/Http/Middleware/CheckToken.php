<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckToken
{
    public function handle(Request $request, Closure $next): Response
    {
        // Get token from different possible header locations
        $token = $request->header('token') ?? 
                 $request->header('Token') ?? 
                 $request->bearerToken() ??
                 $request->query('token');
                 
        \Log::info('Auth attempt: path=' . $request->path() . ', token=' . ($token ? 'provided' : 'not provided'));
        
        if (!$token) {
            \Log::warning('Auth failed: No token provided for path=' . $request->path());
            return response()->json([
                'status' => false,
                'message' => 'Token not provided',
            ], 401);
        }
        
        $user = User::where('remember_token', $token)->first();
        
        if (!$user) {
            \Log::warning('Auth failed: Invalid token: ' . substr($token, 0, 10) . '...');
            return response()->json([
                'status' => false,
                'message' => 'Invalid token',
            ], 401);
        }
        
        \Log::info('Auth success: user_id=' . $user->id . ', email=' . $user->email);
        
        // Set the authenticated user manually
        auth()->login($user);
        
        // Store user ID in the request for direct access
        $request->merge(['authenticated_user_id' => $user->id]);
        
        return $next($request);
    }
}
