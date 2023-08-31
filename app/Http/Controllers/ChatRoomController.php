<?php

namespace App\Http\Controllers;

use App\Events\SendMessgaeEvent;
use App\Models\ChatRoom;
use App\Models\Message;
use App\Traits\GeneralTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
            $chat_rooms = ChatRoom::where('user_id', auth()->user()->id)
                ->orWhere('friend_id', auth()->user()->id)
                ->with(['user' => function ($q) {
                    $q->select('id', 'name', 'image')->where('id', '!=', auth()->user()->id);
                }])
                ->with(['friend' => function ($q) {
                    $q->select('id', 'name', 'image')->where('id', '!=', auth()->user()->id);
                }])
                ->with('last_message:user_id,message,chat_room_id,created_at', 'last_message.user:id,name,image')
                ->withCount('unread_messages')
                // ->orderBy('last_message.created_at', 'desc')
                ->orderBy('updated_at', 'desc')
                ->get();
            return $this->returnData('chat_rooms', $chat_rooms);
        } catch (\Exception $e) {
            return $this->returnError('E001', $e->getMessage());
        }
    }

    public function getChatMessages($chat_room_id)
    {
        try {
            DB::beginTransaction();
            // $chat_room = ChatRoom::where([['friend_id', $friend_id], ['user_id', auth()->user()->id]])
            // ->orWhere([['user_id', $friend_id], ['friend_id', auth()->user()->id]])
            // ->first();
            $chat_room = ChatRoom::find($chat_room_id);
            if (!$chat_room) {
                return $this->returnError('E001', 'you dont have a chat room with this user');
            }
            $messages = $chat_room->messages;
            $messages->where('user_id', '!=', auth()->user()->id)->where('seen', false)->each(function ($message) {
                $message->seen = true;
                $message->save();
            });
            $messages = Message::where('chat_room_id', $chat_room_id)
            ->with('user:id,name,image')
            ->get();
            // $data = [
            //     'user' => auth()->user(),
            //     'friend' => $chat_room->friend,
            //     'messages' => $messages
            // ];
            DB::commit();
            return $this->returnData('messages', $messages);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->returnError('E001', $e->getMessage());
        }
    }

    public function sendMessage(Request $request, $chat_room_id)
    {
        try {
            DB::beginTransaction();
            // $chat_room = ChatRoom::where([['friend_id', $friend_id], ['user_id', auth()->user()->id]])
            //     ->orWhere([['user_id', $friend_id], ['friend_id', auth()->user()->id]])
            //     ->first();

            $chat_room = ChatRoom::find($chat_room_id);

            if (!$chat_room) {
               $chat_room = ChatRoom::create([
                    'user_id' => auth()->user()->id,
                    'friend_id' => $request->friend_id
                ]);
            }
            if (!$request->message) {
                return $this->returnError('E001', 'message is required');
            }
            $chat_room->messages()->create([
                'user_id' => auth()->user()->id,
                'message' => $request->message
            ]);
            $chat_room->updated_at = now();
            event(new SendMessgaeEvent($request->message));
            // event(new SendMessgaeEvent('hello world'));
            DB::commit();
            return $this->returnSuccessMessage('message sent successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->returnError('E001', $e->getMessage());
        }
    }
}
