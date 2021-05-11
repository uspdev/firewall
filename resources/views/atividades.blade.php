@extends('master')

@section('content')

<div class="h4">Registro de atividades</div>
<table class="table table-stripped table-sm table-bordered">
    <thead>
        <tr>
            <th>Data</th>
            <th>codpes</th>
            <th>nome</th>
            <th>Registro</th>
            <th>Descrição</th>
            <th>Agente</th>
        </tr>
    </thead>
    @forelse($activities as $activity)
    <tr>
        <td style="white-space: nowrap;">{{ $activity->created_at->format('d/m/Y H:i:s') }}</td>
        <td>{{ $activity->causer->codpes }}</td>
        <td>{{ $activity->causer->name }}</td>
        <td>{{ $activity->description }}</td>
        <td>{{ $activity->getExtraProperty('descr') }}</td>
        <td>{{ json_encode($activity->getExtraProperty('agent')) }}</td>
    </tr>
    @empty
    @endforelse
</table>

@endsection
