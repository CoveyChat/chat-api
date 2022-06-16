<?php

namespace App\Helpers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Socialite;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Str;
use App\Exceptions\UnauthorizedException;

trait AuthHelpers
{

    protected function resolveAuthFromCredentials($email, $password)
    {
        $user = null;
        if (Auth::attempt(['email' => $email, 'password' => $password])) {
            $user = Auth::user();
        }
        return $this->returnTokenForUser($user);
    }

    protected function resolveAuthFromProvider($provider, $token)
    {
        $user = null;
        try {
            $socialUser = Socialite::driver($provider)->userFromToken($token);
        } catch (ClientException $e) {
            $socialUser = null;
        }

        //Translate into a model
        if (!empty($socialUser->email)) {
            $user = User::where('email', $socialUser->email)->first();

            //Create the SSO user and add them to the guest role
            if (empty($user)) {
                $user = User::create([
                    'name' => $socialUser->name,
                    'email' => $socialUser->email,
                    'password' => bcrypt(Str::uuid()),
                ]);
            }
        }

        return $this->returnTokenForUser($user);
    }

    protected function generateTokenForUser($user)
    {
        if (!empty($user)) {
            return $user->createToken('coveychat')->accessToken;
        } else {
            throw new UnauthorizedException('Invalid Credentials');
        }
    }

    protected function returnTokenForUser($user)
    {
        if (!empty($user)) {
            $success['token'] = $this->generateTokenForUser($user);
            return response()->api()->get($success);
        } else {
            throw new UnauthorizedException('Invalid Credentials');
        }
    }
}
