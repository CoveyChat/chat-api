@extends('layouts.app')

@section('content')
<div class="row">
    <div class="offset-md-4 col-md-4 ">
        <div class="card card-body p-5">
            <form method="POST" action="{{ route('chat_launch', ['chat' => $chat]) }}">
                @csrf

                @if(!empty($messages))
                    <div class="alert alert-danger text-center">{{$messages}}</div>
                @endif
                <input type="password"
                name="password"
                class="form-control form-control-xl text-center"
                placeholder="Enter your chat password"
                />
                <button type="submit" class="btn btn-lg btn-outline-primary btn-block mt-3">
                    {{ __('Verify & Launch') }}
                </button>
            </form>
        </div>
    </div>
</div>
@endsection