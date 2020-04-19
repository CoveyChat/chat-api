@extends('layouts.flexapp')
@section('content')
<chat-component chat-name="{{$chat->name}}"></chat-component>
@endsection
