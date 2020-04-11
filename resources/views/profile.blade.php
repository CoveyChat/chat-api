@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Dashboard</div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    @if(count($chats) == 0)
                        You haven't created any chats yet

                    @else
                        <h1>My Chats</h1>
                        @foreach($chats as $chat)
                            <h2>
                                <a href="{{env('APP_URL')}}/chat/{{$chat->id}}">
                                    <i class="fas fa-external-link-alt"></i> {{$chat->name}}
                                </a>
                            </h2>
                            <p class="text-muted">
                                Chat URL:<br>{{env('APP_URL')}}/chat/{{$chat->id}}
                                <copy-component text="{{env('APP_URL')}}/chat/{{$chat->id}}"></copy-component>
                            </p>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
