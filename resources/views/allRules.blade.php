@extends('master')

@section('title') Firewall @endsection

@section('content')

<h3>Todas as regras @include('partials.datatable-filter')</h3>

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
            <td style="white-space: nowrap;">
                {{ $rule->data ?? '' }}
            </td>
            <td>
            {{ $rule->codpes }}</td>
            <td>{{ $rule->descttd ?? $rule->descr }}</td>
            <td>{{ $rule->source->address ?? '' }}</td>
            <td>
                {{ $rule->destination->address ?? ''}}:{{ $rule->destination->port ?? '-' }}
            </td>
            <td>
                {{-- @if($rule->tipo == 'nat') --}}
                {{ $rule->target }}:{{ $rule->{'local-port'} ?? ''}}
                {{-- @endif --}}
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
    })

</script>
@endsection
