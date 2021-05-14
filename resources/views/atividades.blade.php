@extends('master')

@section('content')

<div class="h4 form-inline">
    Registro de atividades
    <span class="badge badge-pill badge-primary datatable-counter ml-2">-</span>
    @include('partials.datatables-filterbox')
</div>
<table class="table table-stripped table-sm table-bordered atividades">
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
        <td data-sort="{{ $activity->created_at }}" style="white-space: nowrap;">{{ $activity->created_at->format('d/m/Y H:i:s') }}</td>
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

@section('javascripts_bottom')
@parent
<script>
    $(document).ready(function() {
        oTable = $('.atividades').DataTable({
            dom: 't'
            , "paging": false
            , "sort": true
            , "order": [
                [0, "desc"]
            ]
        })

        // vamos renderizar o contador de linhas
        $('.datatable-counter').html(oTable.page.info().recordsDisplay)
    })

</script>
@endsection
