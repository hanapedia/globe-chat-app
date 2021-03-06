<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ChatRoom;
use App\Models\ChatMessage;
use App\Models\ActiveUser;
use Illuminate\Support\Facades\Auth;
use App\Events\NewChatMessage;
use Inertia\Inertia;
use Illuminate\Support\Facades\Http;

class ChatController extends Controller
{
    public function news( Request $request, $topic ){
        $newsApiEndPoint = 'https://newsapi.org/v2/top-headlines';

        $response = Http::withHeaders([
            'X-Api-Key' => 'b3223848c6a34470867e3961b2db38be'
        ])->get($newsApiEndPoint, [
            'q' => $topic
        ]);      

        return $response->json();
    }

    public function searchNews( Request $request, $topic ){
        $newsApiEndPoint = 'https://newsapi.org/v2/everything';

        $response = Http::withHeaders([
            'X-Api-Key' => 'b3223848c6a34470867e3961b2db38be'
        ])->get($newsApiEndPoint, [
            'qInTitle' => $topic,
            'sortBy' => 'popularity',
            'pageSize' => 20,
        ]);      

        return $response->json();
    }

    public function chatroom( Request $request, $roomId ){
        return Inertia::render('Chat/ChatContainer', [
            'roomId' => $roomId
        ]);
    }

    public function rooms( Request $request ){
        return ChatRoom::all();
    }

    public function currentRoom( Request $request, $roomId ){
        return ChatRoom::where('id', $roomId)->get();
    }

    public function messages( Request $request, $roomId ){
        return ChatMessage::where('chat_room_id', $roomId)
            ->with('user')
            ->orderBy('created_at', "DESC")
            ->get();
    }

    public function activeUsers( Request $request, $roomId ){
        return ActiveUser::where('chat_room_id', $roomId)
            ->with('user')
            ->get();
    }

    public function deactivateUser( Request $request, $roomId ){
        return ActiveUser::where('chat_room_id', $roomId)
            ->where('user_id', Auth::id())
            ->delete();
    }

    public function newMessage( Request $request, $roomId ){
        $newMessage = new ChatMessage;
        $newMessage->user_id = Auth::id();
        $newMessage->chat_room_id = $roomId;
        $newMessage->message = $request->message;
        $newMessage->link = $request->link;
        $newMessage->article = json_encode($request->article);
        $newMessage->replying_to = $request->replyTo;
        $newMessage->save();

        broadcast(new NewChatMessage( $newMessage ))->toOthers();


        return $newMessage;
    }

    public function newRoom( Request $request ){ //request includes the object with three elements
        $newRoom = new ChatRoom;
        $newRoom->name = $request->roomName;
        $newRoom->topic = $request->roomTopic;
        $newRoom->description = $request->roomDescription;
        
        if ($request->hasFile('roomPhoto')) {
            $request->validate([
                'roomPhoto' => 'mimes:jpg,jpeg,png'
            ]);
            $filename = $request->roomPhoto->getClientOriginalName();
            $path = $request->roomPhoto->storePubliclyAs('useruploads', $filename, 'public');
            $newRoom->photo = '/storage/'.$path;
        }

        $newRoom->save();

        return $newRoom;
    }

    public function newActiveUser( Request $request, $roomId ){
        $newActiveUser = new ActiveUSer;
        $newActiveUser->chat_room_id = $roomId;
        $newActiveUser->user_id = Auth::id();
        $newActiveUser->region = $request->region;
        $newActiveUser->model_id = $request->modelId;
        $newActiveUser->save();
        
        return $newActiveUser;
    }

    public function newDemoActiveUser( Request $request, $roomId ){
        $newDemoActiveUser = new ActiveUSer;
        $newDemoActiveUser->chat_room_id = $roomId;
        $newDemoActiveUser->user_id = $request->userId;
        $newDemoActiveUser->region = $request->region;
        $newDemoActiveUser->model_id = 1;
        $newDemoActiveUser->save();
        
        return $newDemoActiveUser;
    }

    public function deactivateDemoUser( Request $request, $roomId ){
        return ActiveUser::where('chat_room_id', $roomId)
            ->where('model_id', 1)
            ->delete();
    }

    public function newDemoMessage( Request $request, $roomId ){
        $newDemoMessage = new ChatMessage;
        $newDemoMessage->user_id = $request->demoUserId;
        $newDemoMessage->chat_room_id = $roomId;
        $newDemoMessage->message = $request->message;
        $newDemoMessage->link = $request->link;
        $newDemoMessage->article = $request->article;
        $newDemoMessage->replying_to = $request->replyTo;
        $newDemoMessage->save();

        // broadcast(new NewChatMessage( $newDemoMessage ))->toOthers();

        return $newDemoMessage;
    }
}
