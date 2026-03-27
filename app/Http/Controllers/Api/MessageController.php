<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Message;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $messages = Message::with(['sender', 'receiver'])
            ->where('sender_id', $user->id)
            ->orWhere('receiver_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($messages);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'subject'     => 'nullable|string|max:255',
            'content'     => 'required|string',
        ]);

        $data['sender_id'] = $request->user()->id;

        $message = Message::create($data);

        return response()->json($message->load(['sender', 'receiver']), 201);
    }

    public function show(Request $request, Message $message)
    {
        $this->authorize('view', $message);

        return response()->json($message->load(['sender', 'receiver']));
    }

    public function markRead(Request $request, Message $message)
    {
        $this->authorize('update', $message);

        $message->update(['is_read' => true]);

        return response()->json($message);
    }

    public function unreadCount(Request $request)
    {
        $count = Message::where('receiver_id', $request->user()->id)
            ->where('is_read', false)
            ->count();

        return response()->json(['count' => $count]);
    }
}
