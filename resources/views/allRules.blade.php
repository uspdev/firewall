@extends('master')

@section('content')

<div class="h4 form-inline">
    Todas as regras
    <span class="badge badge-pill badge-primary datatable-counter ml-2">-</span>
    @include('partials.datatables-filterbox')
</div>

<table class="table table-stripped table-sm table-bordered regras">
    <thead>
        <tr>
            <th>Data</th>
            <th>Codpes</th>
            <th>Descrição</th>
            <th>Origem</th>
            <th>Destino</th>
            <th>Alvo</th>
            <th>Criação da regra</th>
        </tr>
    </thead>
    <tbody>
        {{-- @dd($rules) --}}
        @foreach($rules as $rule)
        <tr>
            <td data-sort="{{ $rule->data }}" style="white-space: nowrap;">
                {{ $rule->data ? $rule->data->format('d/m/Y') : '' }}
            </td>
            <td>{{ $rule->codpes }}</td>
            <td>{{ $rule->descttd ?? $rule->descr }}</td>
            <td>{{ $rule->source->address ?? '' }}</td>
            <td>
                {{ $rule->destination->address ?? ''}}:{{ $rule->destination->port ?? '-' }}
            </td>
            <td>
                {{ $rule->target }}:{{ $rule->{'local-port'} ?? ''}}
            </td>
            <td>
                @if(!empty($rule->updated))
                {{ str_replace(' (Local Database)', '', $rule->updated->username) }}
                em {{ date('d/m/Y', $rule->updated->time) }}
                @endif
            </td>
        </tr>
        @endforeach
    </tbody>
</table>

@endsection

@section('javascripts_bottom')
@parent
<script>
    $(document).ready(function() {
        oTable = $('.regras').DataTable({
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
