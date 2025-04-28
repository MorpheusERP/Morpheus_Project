<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VerificaTipoUsuario
{
    public function handle(Request $request, Closure $next)
    {


        $userTipo = Auth::user()->tipo_Usuario;


        // Se for outro tipo (ex: admin), permite o acesso
        return $next($request);
    }
}
