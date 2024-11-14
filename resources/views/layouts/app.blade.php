@extends('laravel-usp-theme::master')

{{-- Blocos do laravel-usp-theme --}}
{{-- Ative ou desative cada bloco --}}

{{-- Target:card-header; class:card-header-sticky --}}
{{-- @include('laravel-usp-theme::blocos.sticky') --}}

{{-- Target: button, a; class: btn-spinner, spinner --}}
{{-- @include('laravel-usp-theme::blocos.spinner') --}}

{{-- Target: table; class: datatable-simples --}}
@include('laravel-usp-theme::blocos.datatable-simples')

{{-- Fim de blocos do laravel-usp-theme --}}

@section('styles')
@parent
  <style>
    .card-header {
      padding-top: 6px;
      padding-bottom: 6px;
    }
  </style>
@endsection
