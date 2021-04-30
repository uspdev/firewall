@extends('master')

@section('title') Firewall @endsection

@section('content')

<div class="h3">
    Endereço IP atual: {{ $user->ip }}<br>
</div>
<br>
<div class="h3">
    {{ config('app.host') }}
</div>
@include('partials.rules')

@endsection

@section('javascripts_bottom')
@parent
{{-- Seu código .js --}}
@endsection
