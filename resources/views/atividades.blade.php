@extends('layouts.app')

@section('content')

<div class="h4">
  Registro de atividades
</div>
<table class="table datatable-simples dt-fixed-header responsive table-stripped table-sm table-bordered atividades">
  <thead>
    <tr>
      <th>Data</th>
      <th>codpes</th>
      <th>nome</th>
      <th>Registro</th>
      <th>Descrição</th>
      <th>Propriedades</th>
    </tr>
  </thead>
  @forelse($activities as $activity)
    @php
      $prop = $activity->properties;
      unset($prop['descr']);
    @endphp
    <tr>
      <td data-sort="{{ $activity->created_at }}" style="white-space: nowrap;">
        {{ $activity->created_at->format('d/m/Y H:i:s') }}
      </td>
      <td>{{ $activity->causer->codpes }}</td>
      <td>{{ $activity->causer->name }}</td>
      <td>{{ $activity->description }}</td>
      <td>{{ $activity->getExtraProperty('descr') }}</td>
      <td>{{ $prop }}</td>
    </tr>
  @empty
  @endforelse
</table>

@endsection