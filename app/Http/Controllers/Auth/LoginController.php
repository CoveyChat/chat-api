<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Laravel\Socialite\Facades\Socialite;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    /**
     * Redirect the user to the GitHub authentication page.
     *
     * @return \Illuminate\Http\Response
     */
    public function redirectToProvider($provider)
    {
        return Socialite::driver($provider)->redirect();
    }

    /**
     * Obtain the user information from GitHub.
     *
     * @return \Illuminate\Http\Response
     */
    public function handleProviderCallback($provider)
    {
        $user = Socialite::driver($provider)->user();
        print_r($provider ?? "NULL");
        echo "<br /><br />";
        print_r($user);



        /*$url = config('app.url');
        error_log($user->token);
        $http = new Client();
        // get the user object from Socialite
        //$user = Socialite::driver($provider)->stateless()->user();
        // return the Laravel Passport access token response
        return $http->post("$url/oauth/token", [
            RequestOptions::FORM_PARAMS => [
                'grant_type' => 'social', // static 'social' value
                'client_id' => env('GOOGLE_CLIENT_ID'), // client id
                'client_secret' => env('GOOGLE_CLIENT_SECRET'), // client secret
                'provider' => $provider, // name of provider (e.g., 'facebook', 'google' etc.)
                'access_token' => $user->token, // access token issued by specified provider
            ],
            RequestOptions::HTTP_ERRORS => false,
        ]);
*/






        //return $user->token;
    }
}
