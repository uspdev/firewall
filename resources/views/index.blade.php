@extends('layouts.app')

@section('content')
  @can('admin')
    @include('partials.serverinfo')
  @endcan
  <br>
  @include('partials.rules')
  <br>
  @include('partials.activity')
  
@endsection
