<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\sk_usuarios as user;
use Illuminate\Support\Facades\Auth;
use \Carbon\Carbon;


class AuthController extends Controller
{

    public function updatepass(){
        $allusers = user::get();
        $resp = [];
        foreach($allusers as $user){
            $resp[] = $user->pass_usuario;
            $usert = user::find($user->id);
            if($usert->pass_usuario)
            {
                $usert->password = bcrypt($user->pass_usuario);
            }
            $usert->save();
        }
        return 'All Ok';

    }


    public function signUp(Request $request)
    {
        $request->validate([
            'nom_usuario' => 'required|string',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|string'
        ]);

        user::create([
            'nom_usuario' => $request->nom_usuario,
            'email' => $request->email,
            'password' => bcrypt($request->password)
        ]);

        return response()->json([
            'message' => 'Successfully created user!'
        ], 201);
    }
  
    /**
     * Inicio de sesión y creación de token
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
            'remember_me' => 'boolean'
        ]);

        $credentials = request(['email', 'password']);

        if (!Auth::attempt($credentials))
            return response()->json([
                'message' => 'Invalid credentials'
            ], 401);

        $user = $request->user();
        $tokenResult = $user->createToken('Personal Access Token');

        $token = $tokenResult->token;
        if ($request->remember_me)
            $token->expires_at = Carbon::now()->addWeeks(1);
        $token->save();

        return response()->json([
            'access_token' => $tokenResult->accessToken,
            'token_type' => 'Bearer',
            'expires_at' => Carbon::parse($token->expires_at)->toDateTimeString()
        ]);
    }
  
    /**
     * Cierre de sesión (anular el token)
     */
    public function logout(Request $request)
    {
        $request->user()->token()->revoke();

        return response()->json([
            'message' => 'Successfully logged out'
        ]);
    }
  
    /**
     * Obtener el objeto User como json
     */
    public function user(Request $request)
    {
        return response()->json($request->user());

        //return "Respuesta";        
    }
}
