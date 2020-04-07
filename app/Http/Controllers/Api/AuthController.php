<?php

namespace App\Http\Controllers\Api;


use App\Http\Controllers\Controller;
use App\Helpers\AuthHelpers;
use App\Http\Requests\Auth\AuthLoginRequest;
use App\Exceptions\UnauthorizedException;

class AuthController extends Controller
{
    use AuthHelpers;
    /**
     * Redirect the user to the GitHub authentication page.
     *
     * @return \Illuminate\Http\Response
     */
    public function validateProviderToken($provider, $token) {
        return $this->resolveAuthFromProvider($provider, $token);
    }

    public function login(AuthLoginRequest $request){
        return $this->resolveAuthFromCredentials($request->auth['email'], $request->auth['password']);
    }

    public function invalid() {
        throw new UnauthorizedException();
    }


}
