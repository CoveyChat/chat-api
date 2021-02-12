<?php

namespace App\Http\Controllers\Api\Chat;

use App\Exceptions\NotFoundException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Chat\ChatCreateRequest;
use App\Http\Requests\Chat\ChatPasswordVerifyRequest;
use App\Http\Requests\Chat\ChatUpdateRequest;
use App\Http\Resources\ChatResource;
use App\Models\Chat;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Request;

class ChatController extends Controller
{

    public function get(Chat $chat)
    {
        return response()->api()->get(new ChatResource($chat));
    }

    public function getUserChat($user_id = null)
    {
        $user = Auth::user();

        if(empty($user) || $user->id != $user_id) {
            throw new NotFoundException();
        } else {
            return response()->api()->get(ChatResource::collection(Chat::where('created_by', $user->id)->orderBy('created_at', 'DESC')->get()));
        }

    }

    public function create(ChatCreateRequest $request, $user = null)
    {
        //No user passed, try and see if we are logged in
        if(empty($user)) {
            $user = Auth::user();
        }

        $chat = new Chat([
            'created_by' => $user->id ?? null,
            'name' => $request->name,
            'description' => $request->description ?? null,
            'password' => (!empty($request->password) ? Hash::make($request->password) : null),
        ]);

        $chat->save();

        return response()->api()->created(new ChatResource($chat));

    }

    public function createUserChat(ChatCreateRequest $request, $user_id)
    {
        $user = Auth::user();

        return $this->create($request, $user);
    }

    public function update(ChatUpdateRequest $request, $user_id, Chat $chat)
    {
        $chat->name = $request->name ?? '';
        $chat->description = $request->description ?? '';
        $chat->password = $request->password ?? '';

        $chat->save();

        return $chat;
    }

    public function delete(Request $request, $user_id, Chat $chat)
    {
        $chat->delete();
        return response()->api()->deleted();
    }
}
