<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Conversation;
use App\Models\Message;

class MessagesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    public function store(Request $request, Conversation $conversation)
    {
        $validatedData = $request->validate([
            'content' => 'required|string',
            'image' => 'nullable|image|max:2048',
        ]);

        // 'conversation_id' being passed in the request means the message...
        // ...is being definitely sent from within an EXISTING conversation (conversations.show)
        if($request->has("conversation_id")){
            // [security measure] Checks if the message sender is one of the two involved in the conversation
            $conversation = Conversation::find($request->input("conversation_id"));
            if(!$conversation->users->contains(auth()->user())){
                return Redirect("/conversations")->with("error", "UNAUTHORIZED ACCESS");
            }
            else 
                $conversation_id = $request->input("conversation_id");
        }
        else{
            // Creates a new conversation after checking if there's already an existing one (conversations.index)

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

            $conversation_id = $conversation->id;
        }

        // Creates a new message and assigns it to the conversation
        $message = new Message;
        $message->user_id = auth()->user()->id;
        $message->conversation_id = $conversation_id;
        $message->content = $request->input("content");
        $message->save();

        // If the request contains an image file, it will be assign to the newly created message
        if ($request->hasFile('image')) {
            $filenameWithExtension = $request->file("image")->getClientOriginalName();
            $extension = $request->file("image")->getClientOriginalExtension();
            $filenameWithoutExtension = pathinfo($filenameWithExtension, PATHINFO_FILENAME);
            $filenameToStore = $filenameWithoutExtension."_".time()."_c".$conversation_id."_m".$message->id."_u".(auth()->user()->id).".".$extension;

            $convo_dir = "public/conversations/convo_".$conversation->id;
            $request->file("image")->storeAs($convo_dir, $filenameToStore);
            
            $message->image = $filenameToStore;
            $message->save();
        }

        return redirect()->route('conversations.show', $conversation);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    // write a fucntion to block the other user from sending messages

    public function blockUser(Request $request, Conversation $conversation)
    {
        $conversation->users()->updateExistingPivot(auth()->user()->id, ['blocked' => true]);
        return redirect()->route('conversations.show', $conversation);
    }
}
