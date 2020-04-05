<?php

namespace App\Http\Controllers\Chat;

use App\Http\Controllers\Controller;
use App\Http\Requests\Chat\ChatCreateRequest;
use App\Models\Chat;
use Illuminate\Http\Request;
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
        $chat = new Chat([
            'name' => $request->name,
            'description' => $request->description,
            'password' => (!empty($request->password) ? Hash::make($request->password) : null),
        ]);

        $chat->save();

        return redirect()->route('chat_launch', ['chat' => $chat->id]);
    }

    public function launch(Chat $chat)
    {
        if(empty($chat->password)) {
            return view('chat.launch');
        } else {
            return view('chat.verify_launch');
        }
    }
}
