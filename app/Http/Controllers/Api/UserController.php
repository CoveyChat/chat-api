<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\DataConflictException;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Http\Requests\Chat\ChatCreateRequest;
use App\Models\Chat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Helpers\AuthHelpers;
use App\Http\Requests\User\ForgotPasswordRequest;
use App\Http\Requests\User\RegisterRequest;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Password;

class UserController extends Controller
{
    use AuthHelpers;
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //$this->middleware('auth');
    }

    public function whoami(Request $request)
    {
        $user = Auth::user();
        $user->token = $this->generateTokenForUser($user);
        return response()->api()->get(['user' => $user]);
    }

    public function register(RegisterRequest $request)
    {
        $newUser = $request->user;
        $newUser['password'] = bcrypt($request->user['password']);
        $user = collect([]);

        try {
            $user = User::create($newUser);
        } catch (QueryException $e){
            throw new DataConflictException($e->errorInfo[2]);
        }

        return response()->api()->created(['user' => $user]);
    }

    public function passwordForgot(ForgotPasswordRequest $request)
    {
        Password::sendResetLink($request->user);

        //return response()->json(["msg" => 'Reset password link sent on your email id.']);

        return response()->api()->get(null, 'A reset password link has been sent');
    }
}
