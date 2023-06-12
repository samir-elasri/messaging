@extends('layouts.app')

@section('content')
<div class="col-10 mx-auto">

    {{-- list of messages: --}}
    @if (!empty($messages))
        @foreach ($messages as $message)
            @if ($message->user_id == Auth::user()->id)
                <div class="row col-8 d-flex mt-2 offset-4">
            @else
                <div class="row col-8 d-flex mt-2">
            @endif
                    <div class="col-2 w-100">
                        <p class="w-100 text-center"><a href="/users/{{$message->user->id}}">{{$message->user->name}}</a></p>
                    </div>
                    <div class="col-10 w-100 align-self-center">
                        <p class="text-message">{{$message->content}}</p>
                        @if (!is_null($message->image))
                            <img class="w-100 p-2" src="{{$message->photo()}}">
                        @endif
                        <span class="w-100 d-inline-block text-right">{{$message->created_at}}</span>
                        <div class="row w-100">
                            <div class="col-2 offset-11 w-100">
                                @if ($message->is_read)
                                    read
                                @else
                                    unread
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
        @endforeach
    @endif
    <hr>

    {{-- send new message: --}}
    <div class="col-12 mt-2 mx-auto">
        <form method="POST" action="/messages" enctype="multipart/form-data">
            @csrf
            <input hidden name="conversation_id" value="{{$conversation->id}}">
            <div class="form-row">
                <div class="col-9 mb-2">
                    <textarea name="content" cols="2" class="form-control"></textarea>
                </div>
                <div class="col-3">
                    <div class="row">
                        <div class="col mb-1 w-100">
                            <input type="file" name="image" class="form-control">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col w-100">
                            <button class="form-control" type="submit">send message</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        <div class="row">
            <div class="col w-100 mt-1">
                <form method="POST" action="/conversations/{{$conversation->id}}">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-danger" type="submit">Delete conversation</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection