<table class="table table-stripped table-sm table-bordered">
    <thead>
        <tr>
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
            <td>{{ $rule->source->address }}</td>
            <td>{{ $rule->destination->address ?? ''}}:{{ $rule->destination->port ?? '-' }}</td>
            <td>
                @if($rule->tipo == 'nat')
                {{ $rule->target }}:{{ $rule->{'local-port'} ?? ''}}
                @endif
            </td>
            <td>{{ $rule->descr }}</td>
            <td>
                @if($rule->source->address != $user->ip)
                <form method="POST" action="updateRules">
                    @csrf
                    <input type="hidden" name="associated-rule-id" value="{{ $rule->{'associated-rule-id'} ?? ''}}">
                    <input type="hidden" name="descr" value="{{ $rule->descr ?? ''}}">

                    @if($rule->tipo == 'nat')
                    <input type="hidden" name="acao" value="atualizarNat">
                    @else
                    <input type="hidden" name="acao" value="atualizarFilter">
                    @endif

                    <button type="submit" name="submit" value="Atualizar">Atualizar</button>
                </form>
                @else
                Acesso liberado
                @endif
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
