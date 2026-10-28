<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('warning', 'Silakan login terlebih dahulu untuk mengakses sistem.');
        }

        $user = Auth::user();

        // If specific roles required
        if (!empty($roles) && !in_array($user->role, $roles)) {
            // If user is doctor attempting to access admin route, redirect to doctor dashboard
            if ($user->role === 'dokter') {
                return redirect()->route('dokter.dashboard')->with('error', 'Anda tidak memiliki hak akses ke halaman administratif.');
            }

            // If admin attempting to access doctor route, redirect to admin dashboard
            if (in_array($user->role, ['super_admin', 'admin_sdm'])) {
                return redirect()->route('dashboard')->with('error', 'Halaman ini khusus untuk staf medis / dokter.');
            }

            abort(403, 'Akses Ditolak: Anda tidak memiliki izin untuk mengakses halaman ini.');
        }

        return $next($request);
    }
}
