<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use Illuminate\Http\Request;
use App\Models\Message;
use App\Models\User;

class ConversationsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Delete conversations that have been opened but remained empty when the inbox is opened (conversations.index)
        foreach (auth()->user()->conversations as $conversation) {
            //dd($conversation->pivot->deleted_at);
            if(count($conversation->messages) == 0){
                $conversation->delete();
            }
            elseif ($conversation->pivot->deleted_at && $conversation->messages()->where('is_read', false)->exists()) {
                $conversation->pivot->deleted_at = null;
                $conversation->pivot->save();
            }
        }

        $authUserId = auth()->user()->id;
        $users = User::whereNotIn('id', [$authUserId])->get();

        // Shows only the conversations that are not soft-deleted
        $conversations = Conversation::whereHas('users', function ($query) use ($authUserId) {
            $query->where('users.id', $authUserId)
                ->whereNull('conversation_user.deleted_at');
        })
        ->with(['users', 'messages'])
        ->orderBy('updated_at', 'desc')
        ->get();

        return view('conversations.index', compact('conversations','users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'user_id' => 'required|string'
        ]);

        // Creates a new conversation after checking if there's already an existing one (profiles.show)

        $authenticatedUserId = auth()->user()->id;
        $otherUserId = $request->input("user_id");

        $conversations = Conversation::whereHas('users', function ($query) use ($authenticatedUserId) {
            $query->where('users.id', $authenticatedUserId);
        })->whereHas('users', function ($query) use ($otherUserId) {
            $query->where('users.id', $otherUserId);
        })->get();

        if ($conversations->isEmpty()) {
            
          // Create a new conversation
          $conversation = new Conversation();
          $conversation->save();

          // Attach the authenticated user and the other user to the conversation
          $conversation->users()->attach([$authenticatedUserId, $otherUserId]);
        } else {
            $conversation = Conversation::find($conversations->first()->id);
        }

        return redirect()->route('conversations.show', $conversation);
    }

    /**
     * Display the specified resource.
     */
    public function show(Conversation $conversation)
    {
        // $conversationUser is the pivot model for the user and conversation relationship
        // ...While $user is the authenticated user that made the request.

        $user = auth()->user();
        $conversationUser = $conversation->users()->where('user_id', $user->id)->firstOrFail();

        // Mark all messages as read up to the last read message
        $conversation->messages()
            ->where('user_id', '!=', $user->id)
            ->where('id', '>', $conversationUser->last_read_message_id ?? 0)
            ->update(['is_read' => true]);

        // Retrieve only the new messages in the conversation
        $lastReadMessageId = $user->conversations()->where('conversation_id', $conversation->id)->first()->pivot->last_read_message_id;
        
        $newMessages = $conversation->messages()
            ->where('id', '>', $lastReadMessageId ?? 0)
            ->get();

        $messages = $newMessages;

        return view('conversations.show', compact('conversation', 'messages'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Conversation $conversation)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Conversation $conversation)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Conversation $conversation)
    {
        $user = auth()->user();
        
        $lastMessage = $conversation->messages()->latest()->first();
        $conversation->users()->updateExistingPivot($user->id, ['last_read_message_id' => $lastMessage->id]);

        $conversation->users()->where('user_id', auth()->user()->id)->update(['deleted_at' => now()]);
        return redirect()->route('conversations.index');
    }
}
