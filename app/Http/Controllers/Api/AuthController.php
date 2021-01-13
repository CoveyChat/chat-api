<?php

namespace App\Http\Controllers\Api;


use App\Http\Controllers\Controller;
use App\Helpers\AuthHelpers;
use App\Http\Requests\Auth\AuthLoginRequest;
use App\Exceptions\UnauthorizedException;
use Illuminate\Support\Facades\Auth;

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

    public function logout() {
        $user = Auth::user();
        $userTokens = $user->tokens;

        foreach($userTokens as $token) {
            $token->revoke();
        }

        return response()->api()->deleted();
        //Auth::user()->invalidate(true);
    }

    public function get(){
        $user = Auth::user();
        if(!empty($user)) {
            return $this->returnTokenForUser($user);
        } else {
            throw new UnauthorizedException();
        }
    }

    public function invalid() {
        throw new UnauthorizedException();
    }


}
