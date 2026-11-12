<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CekHakAkses
{
    public function handle(Request $request, Closure $next, string $hak): Response
    {
        abort_unless($request->user()?->can($hak), 403, 'Kamu tidak punya hak akses untuk halaman ini.');

        return $next($request);
    }
}
