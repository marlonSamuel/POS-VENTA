<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\ApiController;
use App\Http\Controllers\BaseController as BaseController;
use App\Models\User;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;

class AuthController extends ApiController
{  
    public function __construct()
    {
        $this->middleware('auth:api')->except(['login']);
    }

    /**
     * Login api
     *
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request)
    {
        $request->validate([
            'username'       => 'required',
            'password'    => 'required|string',
        ]);

        if (!Auth::attempt(['username' => $request->username, 'password' => $request->password])) {
                return $this->errorResponse('Usuario o contraseña incorrectas',401);
        }

        //$credentials = request(['email', 'password']);

        $http = new Client(
            [
                'verify' => false
            ]
        );

        $uri = config('services.passport.login_endpoint').'/oauth/token';

        $response = $http->post($uri, [
            'form_params' => [
                'grant_type' => 'password',
                'client_id' => config('services.passport.client_id'),
                'client_secret' => config('services.passport.client_secret'),
                'username' => $request->username,
                'password' => $request->password,
                'scope' => '*',
            ],
        ]);

        $resp = json_decode((string) $response->getBody(), true);

        return response()->json([
            'token_type' => $resp['token_type'],
            'expires_in' => Carbon::parse($resp['expires_in'])->toDateTimeString(),
            'access_token' => $resp['access_token'],
            'refresh_token' => $resp['refresh_token'],
            'user' => $request->user(),
            'code'=> 200
        ]);
    }

    //cerrar session
    public function logout(Request $request)
    {
        $request->user()->token()->revoke();
        return response()->json(['message' => 
            'saliendo...']);
    }

    //obtener usuario logueado
    public function user(Request $request)
    {
        $user = $request->user();
        return response()->json($user);
    }
}
