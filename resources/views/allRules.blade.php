@extends('layouts.app')

@section('content')

<div class="h4 form-inline">
  Todas as regras
</div>

<table class="table datatable-simples dt-fixed-header responsive table-stripped table-sm table-bordered regras">
  <thead>
    <tr>
      <th>Data</th>
      <th>Codpes</th>
      <th>Descrição</th>
      <th>Origem</th>
      <th>Destino</th>
      <th>Alvo</th>
      <th>Criação da regra</th>
      <th>Tipo</th>
    </tr>
  </thead>
  <tbody>
    @foreach($rules as $rule)
      <tr>
        <td data-sort="{{ $rule->data }}" style="white-space: nowrap;">
          {{ $rule->data ? $rule->data->format('d/m/Y') : '' }}
        </td>
        <td>{{ $rule->codpes }}</td>
        <td>{{ $rule->descttd ?? $rule->descr }}</td>
        <td>{{ $rule->source->address ?? '' }}</td>
        <td>
          {{ $rule->destination->address ?? '' }}:{{ $rule->destination->port ?? '-' }}
        </td>
        <td>
          @if($rule->tipo == 'nat')
            {{ $rule->target }}:{{ $rule->{'local-port'} ?? '' }}
          @endif
        </td>
        <td>
          @if(!empty($rule->updated))
            {{ str_replace(' (Local Database)', '', $rule->updated->username) }}
            em {{ date('d/m/Y', $rule->updated->time) }}
          @endif
        </td>
        <td>{{ $rule->tipo }}</td>
      </tr>
    @endforeach
  </tbody>
</table>

@endsection
