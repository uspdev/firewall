@extends('layouts.app')

@section('content')
  @includeWhen(Gate::check('admin'),'partials.serverinfo')
  @include('partials.rules')
  @include('partials.activity')
@endsection
