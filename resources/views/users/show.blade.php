@extends('layouts.app')

@section('content')
    <div class="text-center">
        <h1>{{ $user->name }}</h1>
        <h2>{{ $user->email }}</h2>
    </div>

    @if (!Auth::guest())
        @if (Auth::user()->id != $user->id)
            <div class="col-md-4 col-lg-6 mx-auto text-center">
                <form method="POST" action="/conversations">
                    @csrf
                    <input hidden name="user_id" value="{{$user->id}}">
                    <button class="cr-btn w-50" type="submit">send message</button>
                </form>
            </div>
        @endif
    @endif
@endsection
