<?php

namespace App\Http\Controllers\Chat;

use App\Http\Controllers\Controller;
use App\Http\Requests\Chat\ChatCreateRequest;
use App\Http\Requests\Chat\ChatPasswordVerifyRequest;
use App\Models\Chat;
use GuzzleHttp\Psr7\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ChatController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {

    }

    public function index()
    {
        return view('chat.create');
    }

    public function create(ChatCreateRequest $request)
    {
        $user = Auth::user();



        $chat = new Chat([
            'created_by' => $user->id ?? null,
            'name' => $request->name,
            'description' => $request->description,
            'password' => (!empty($request->password) ? Hash::make($request->password) : null),
        ]);

        $chat->save();
        if(!empty($user)) {
            return redirect()->route('profile');
        } else {
            return redirect()->route('chat_launch', ['chat' => $chat->id, 'messages' => null]);
        }

    }

    public function launch(Chat $chat)
    {
        if(empty($chat->password)) {
            return view('chat.launch', ['chat' => $chat]);
        } else {
            return view('chat.verify_launch', ['chat' => $chat]);
        }
    }

    public function verifyLaunchPassword(Chat $chat, ChatPasswordVerifyRequest $request) {
        if(Hash::check($request->input('password'), $chat->password)) {
            return view('chat.launch', ['chat' => $chat]);
        } else {
            return view('chat.verify_launch', ['chat' => $chat, 'messages' => 'Incorrect password']);
        }

    }
}
