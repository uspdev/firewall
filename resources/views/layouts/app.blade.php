@extends('laravel-usp-theme::master')

{{-- Blocos do laravel-usp-theme --}}
{{-- Ative ou desative cada bloco --}}

{{-- Target:card-header; class:card-header-sticky --}}
@include('laravel-usp-theme::blocos.sticky')

{{-- Target: button, a; class: btn-spinner, spinner --}}
@include('laravel-usp-theme::blocos.spinner')

{{-- Target: table; class: datatable-simples --}}
@include('laravel-usp-theme::blocos.datatable-simples')

{{-- Fim de blocos do laravel-usp-theme --}}

@section('flash')
  <div class="flash-message fixed-top w-50 ml-auto mr-auto" style="margin-top: 60px">
    @foreach (['danger', 'warning', 'success', 'info'] as $msg)
      @if (Session::has('alert-' . $msg))
        <p class="alert alert-{{ $msg }} border border-dark rounded">{{ Session::get('alert-' . $msg) }}
          <a href="#" class="close" data-dismiss="alert" aria-label="fechar">&times;</a>
        </p>
      @endif
    @endforeach
  </div>

@section('javascripts_bottom')
  @parent
  <script>
    $(function() {
      $(".flash-message").fadeTo(5000, 500).slideUp(500, function() {
        $(".flash-message").slideUp(500);
      });
    })
  </script>
@endsection

@endsection

@section('title') Firewall @endsection