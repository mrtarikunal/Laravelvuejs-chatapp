<?php

namespace App\Http\Controllers;

use App\Events\MsgReadEvent;
use App\Events\PrivateChatEvent;
use App\Http\Resources\ChatResource;
use App\Models\Chat;
use App\Models\Message;
use App\Models\Session;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ChatController extends Controller
{
    public function send(Session $session, Request $request)
    {
       $message = $session->messages()->create(['content' => $request->content]);

        $chat = $message->createForSend($session->id);

        $message->createForRecieve($session->id, $request->to_user);
        //defactor yaptık. model dosyası içinden hallettik hepsini

        broadcast(new PrivateChatEvent($message->content,$chat));

        //return $message;
        return response($chat->id, 200);
    }

    public function chats($session_id)
    {
        $chats = Chat::where('session_id', $session_id)->where('user_id', auth()->id())->get();
        return ChatResource::collection($chats);

        //$session = Session::find($session_id);
       //return $session->chats();
      //  return $chats;
    }

    public function read($session_id)
    {
        $chats = Chat::where('session_id', $session_id)->where('user_id', '!=', auth()->id())->get()->where('read_at', null)->where('type', 0);

        foreach ($chats as $chat) {
            $chat->update([
                'read_at' => Carbon::now()
            ]);
            broadcast(new MsgReadEvent(new ChatResource($chat), $chat->session_id));
        }
    }

    public function clear($session_id)
    {

        Chat::where('session_id', $session_id)->where('user_id', auth()->id())->delete();

        $chats= Chat::where('session_id', $session_id)->get();
        if($chats->count() == 0)
        {
            Message::where('session_id', $session_id)->delete();

        }

        return response('cleared', 200);
    }
}
