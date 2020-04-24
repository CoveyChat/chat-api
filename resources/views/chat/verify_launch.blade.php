@extends('layouts.flexapp')

@section('content')
<div class="container mt-5">
    <div class="row">
        <div class="col-sm-12 offset-md-3 col-md-6 offset-lg-4 col-lg-4 ">
            <div class="card card-body p-5">
                <form method="POST" action="{{ route('chat_launch', ['chat' => $chat->id]) }}">
                    @csrf

                    @if(!empty($messages))
                        <div class="alert alert-danger text-center">{{$messages}}</div>
                    @endif
                    <input type="password"
                    name="password"
                    class="form-control form-control-xl text-center"
                    placeholder="Chat password"
                    />
                    <button type="submit" class="btn btn-lg btn-outline-primary btn-block mt-3">
                        {{ __('Verify & Launch') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection