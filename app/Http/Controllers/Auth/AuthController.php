<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{

    /**
     * Autentica a un usuario y genera un token de acceso.
     *
     * Este método valida las credenciales recibidas (email y contraseña),
     * intenta iniciar sesión con el guard por defecto y, si la autenticación es exitosa,
     * genera un token personal de acceso mediante Laravel Sanctum.
     *
     * Pasos importantes:
     * - Valida que el campo `email` tenga formato válido y que la contraseña esté presente.
     * - Verifica las credenciales usando `Auth::attempt`.
     * - Si la autenticación falla, lanza una `ValidationException`.
     * - Si la autenticación es exitosa, crea un token de acceso y lo devuelve junto con los datos del usuario.
     *
     * @param \Illuminate\Http\Request $request Datos de la solicitud HTTP con email y contraseña.
     * @return \Illuminate\Http\JsonResponse Respuesta JSON con el token de acceso, tipo de token y datos del usuario.
     * @throws \Illuminate\Validation\ValidationException Si las credenciales son inválidas.
     */
    public function login(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email',
                'password' => 'required'
            ]);

            if (!Auth::attempt($request->only('email', 'password'))) {
                throw ValidationException::withMessages([
                    'email' => ['Credenciales incorrectas'],
                ]);
            }

            $user = $request->user();
            //token de acceso
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'access_token' => $token,
                'token_type' => 'Bearer',
                'user' => $user
            ]);

            return response()->json(['csrf_token' => csrf_token()]);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => $th->getMessage(),
            ]);
        }
    }


    /**
     * Cierra la sesión del usuario autenticado.
     *
     * Este método elimina todos los tokens de acceso asociados al usuario autenticado,
     * cerrando así todas las sesiones activas de la cuenta.
     *
     * Pasos importantes:
     * - Verifica si el usuario está autenticado; si no lo está, devuelve un error 401 (Unauthorized).
     * - Elimina todos los tokens personales asociados al usuario.
     * - Devuelve un mensaje confirmando que la sesión fue cerrada.
     *
     * @param \Illuminate\Http\Request $request Solicitud HTTP con información del usuario autenticado.
     * @return \Illuminate\Http\JsonResponse Respuesta JSON confirmando el cierre de sesión o error.
     */
    public function logout(Request $request)
    {
        try {
            if (!$request->user()) {
                return response()->json([
                    'message' => 'Usuario no autenticado'
                ], 401); // 401 = Unauthorized
            }

            //elimina el token de acceso
            $request->user()->tokens()->delete();

            return response()->json([
                'message' => 'Sesión cerrada exitosamente'
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                "error" => "Se generó un error: " . $th->getMessage(),
            ], 500);
        }
    }


    
    public function resetPassword(Request $request){
        try {
            $request->validate([
                "email" => "required|email|exists:users,email",
                "new_password" => "required|min:8|confirmed"
            ]);

            $user = User::where('email', $request->email)->first();

            if (!$user) {
                return response()->json([
                    "message" => "No existe un usuario con ese correo."
                ], 404);
            }

            $user->password = Hash::make($request->new_password);
            $user->save();

            return response()->json([
                "message" => "Contraseña actualizada exitosamente. Ahora puede iniciar sesion"
            ]);

        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Error al restablecer la contraseña',
                'error' => $th->getMessage()
            ], 500);
        }
    }
}
