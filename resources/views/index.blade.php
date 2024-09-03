@extends('layouts.app')

@section('title') Firewall @endsection

@section('content')


<div class="h3">
    Endereço IP atual: {{ $user->ip }}<br>
</div>
<br>
@include('partials.rules')
<br>
@include('partials.activity')
@endsection

