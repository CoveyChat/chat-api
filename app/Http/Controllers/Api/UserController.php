<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Http\Requests\Chat\ChatCreateRequest;
use App\Models\Chat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Helpers\AuthHelpers;

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
        $this->middleware('auth');
    }

    public function whoami(Request $request)
    {
        $user = Auth::user();
        $user->token = $this->generateTokenForUser($user);
        return response()->api()->get($user);
    }


}
