<?php

namespace App\Http\Controllers\Api\Chat;

use App\Exceptions\NotFoundException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Chat\ChatCreateRequest;
use App\Http\Requests\Chat\ChatPasswordVerifyRequest;
use App\Http\Resources\ChatResource;
use App\Models\Chat;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;


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
            return response()->api()->get(ChatResource::collection(Chat::where('created_by', $user->id)->get()));
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
            'name' => $request->chat['name'],
            'description' => $request->chat['description'] ?? null,
            'password' => (!empty($request->chatp['password']) ? Hash::make($request->chat['password']) : null),
        ]);

        $chat->save();

        return response()->api()->created(new ChatResource($chat));

    }

    public function createUserChat(ChatCreateRequest $request, $user_id)
    {
        $user = Auth::user();

        return $this->create($request, $user);
    }

    public function update(ChatUpdateRequest $request)
    {
        echo "TEST update";
        exit;

    }

    public function delete()
    {
        echo "TEST delete";
        exit;

    }
}
