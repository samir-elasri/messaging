@extends('layouts.app')

@section('content')
    <div class="col-md-10 mx-auto text-align-center">

        {{-- send new message: --}}
        <h4 class="pt-2 pb-2">Send new message</h4>
        <form method="POST" action="/messaging/messages">
            @csrf
            <div class="form-row">
                <div class="col-md-4">
                    <textarea name="content" cols="4" class="form-control"></textarea>
                </div>
            </div>
            <div class="form-row">
                <div class="col-md-4">
                    <select name="user_id">
                        @foreach ($users as $user)
                            <option value={{$user->id}}>{{$user->name}}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <button type="submit">send message</button>
                </div>
            </div>
        </form>

        {{-- list of conversations: --}}
        @if (count($conversations) > 0)
            <div class="col-md-10 mx-auto">
                <h4 class="pt-4">Conversations</h4>
                @foreach ($conversations as $conversation)
                    <hr>
                    <a href="/messaging/conversations/{{$conversation->id}}">
                        <div class="row d-flex">
                            <div class="col-md-10 w-100 align-self-center">
                                <p>{{$conversation->messages->last()->content}}</p>
                                <span class="d-block w-100 text-right">{{$conversation->messages->last()->created_at}}</span>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        @else
            <h4 class="col-md-10 mx-auto pt-4">No conversations found!</h4>
        @endif
    </div>
@endsection