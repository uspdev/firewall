@extends('layouts.app')

@section('content')
  @can('admin')
    @include('partials.serverinfo')
  @endcan
  @include('partials.rules')
  @include('partials.activity')
@endsection
