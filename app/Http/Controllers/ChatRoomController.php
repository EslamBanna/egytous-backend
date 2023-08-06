<?php

namespace App\Http\Controllers;

use App\Models\ChatRoom;
use App\Traits\GeneralTrait;
use Illuminate\Http\Request;

class ChatRoomController extends Controller
{
    use GeneralTrait;

    public function addChatRoom(Request $request)
    {
        try {
            $check_if_chat_room_is_exist = ChatRoom::where('user_id', auth()->user()->id)
                ->where('friend_id', $request->friend_id)->first();
            if ($check_if_chat_room_is_exist) {
                return $this->returnError('E001', 'you have a chat room with this user');
            }
            ChatRoom::create([
                'user_id' => auth()->user()->id,
                'friend_id' => $request->friend_id
            ]);
            return $this->returnSuccessMessage('chat room added successfully');
        } catch (\Exception $e) {
            return $this->returnError('E001', $e->getMessage());
        }
    }

    public function getChatRooms()
    {
        try {
            $chat_rooms = ChatRoom::where('user_id', auth()->user()->id)->get();
            return $this->returnData('chat_rooms', $chat_rooms);
        } catch (\Exception $e) {
            return $this->returnError('E001', $e->getMessage());
        }
    }

    public function getChatMessages($friend_id)
    {
        try {
            $chat_room = ChatRoom::where('user_id', auth()->user()->id)
                ->where('friend_id', $friend_id)->first();
            if (!$chat_room) {
                return $this->returnError('E001', 'you dont have a chat room with this user');
            }
            $messages = $chat_room->messages;
            return $this->returnData('messages', $messages);
        } catch (\Exception $e) {
            return $this->returnError('E001', $e->getMessage());
        }
    }

    public function sendMessage(Request $request, $friend_id)
    {
        try {
            $chat_room = ChatRoom::where('user_id', auth()->user()->id)
                ->where('friend_id', $friend_id)->first();
            if (!$chat_room) {
                return $this->returnError('E001', 'you dont have a chat room with this user');
            }
            $chat_room->messages()->create([
                'user_id' => auth()->user()->id,
                'message' => $request->message
            ]);
            return $this->returnSuccessMessage('message sent successfully');
        } catch (\Exception $e) {
            return $this->returnError('E001', $e->getMessage());
        }
    }
}
