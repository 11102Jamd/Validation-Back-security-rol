<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureIsBaker
{
    /**
     * Maneja una solicitud entrante y asegura que el usuario sea Panadero.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {

        //obtener al usuario autenticado si existe
        $user = $request->user();

        //Si el usuario es administrador permite acceso sin restricciones
        if ($user && $user->isAdmin()) {
            return $next($request);
        }

        //si hay un usuario que no es panadero, denegar acceso
        if (!$user || !$user->isBaker()) {
            abort($user ? 403 : 401, $user ? 'Acceso solo para panaderos' : 'No autenticado');
        }

        //continua con la ejecucion si las validaciones son correctas
        return $next($request);
    }
}
