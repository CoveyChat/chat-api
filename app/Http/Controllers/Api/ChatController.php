<?php

namespace App\Http\Controllers\Api;

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

    public function get(Chat $chat)
    {
        return $chat;
    }

    public function update(Request $request, Chat $chat)
    {
        $chat->host = json_encode($request->host);
        $chat->save();
    }
}
