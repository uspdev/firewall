<style>
    #card-rules {
      border: 1px solid #0000FF;
      border-top: 3px solid #0000FF;
    }
</style>
<div class="card" id="card-rules">
    <div class="card-header h4">Regras de acesso - Endereço IP atual: {{ $user->ip }}<br></div>
    <div class="card-body">
        <table class="table table-stripped table-sm table-bordered table-hover">
            <thead>
                <tr>
                    <td>Atualização</td>
                    <td>Origem</td>
                    <td>Destino</td>
                    <td>Alvo</td>
                    <td>Descrição</td>
                    <td></td>
                </tr>
            </thead>
            <tbody>
                {{-- @dd($rules) --}}
                @foreach($rules as $rule)
                <tr>
                    <td>{{ $rule->data ? $rule->data->format('d/m/Y') : '' }}</td>
                    <td>{{ $rule->source->address }}</td>
                    <td>{{ $rule->destination->address ?? ''}}:{{ $rule->destination->port ?? '-' }}</td>
                    <td>
                        @if($rule->tipo == 'nat')
                        {{ $rule->target }}:{{ $rule->{'local-port'} ?? ''}}
                        @endif
                    </td>
                    <td>{{ $rule->descttd }}</td>
                    <td>
                        @if($rule->source->address != $user->ip)
                        <form method="POST" action="updateRules">
                            @csrf

                            @if($rule->tipo == 'nat')
                            <input type="hidden" name="acao" value="atualizarNat">
                            <input type="hidden" name="associated-rule-id" value="{{ $rule->{'associated-rule-id'} ?? ''}}">
                            @else
                            <input type="hidden" name="acao" value="atualizarFilter">
                            <input type="hidden" name="tracker" value="{{ $rule->tracker ?? ''}}">
                            @endif

                            <button type="submit" name="submit" class="btn btn-sm btn-primary" value="Atualizar">Atualizar</button>
                        </form>
                        @else
                        <span class="badge badge-success">Acesso liberado</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
