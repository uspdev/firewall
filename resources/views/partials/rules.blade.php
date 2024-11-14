@section('styles')
  @parent
  <style>
    #card-rules {
      border: 1px solid #0000FF;
      border-top: 3px solid #0000FF;
    }
  </style>
@endsection

<div class="card mt-3" id="card-rules">
  <div class="card-header h4">
    Regras de acesso - endereço IP atual: <span class="text-primary">{{ $user->ip }}</span>
  </div>
  <div class="card-body">
    <table class="table table-stripped table-sm table-bordered table-hover">
      <thead>
        <tr>
          <th>Atualização</th>
          <th>Origem</th>
          <th>Destino</th>
          <th>Alvo</th>
          <th>Descrição</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        @foreach($rules as $rule)
          <tr>
            <td>{{ $rule->data ? $rule->data->format('d/m/Y') : '' }}</td>
            <td>{{ $rule->source->address }}</td>
            <td><b>{{ $rule->destination->address ?? '' }}:{{ $rule->destination->port ?? '-' }}</b></td>
            <td>
              @if($rule->tipo == 'nat')
                {{ $rule->target }}:{{ $rule->{'local-port'} ?? '' }}
              @endif
            </td>
            <td>{{ $rule->descttd }}</td>
            <td>
              @if($rule->source->address != $user->ip)
                <form method="POST" action="updateRules">
                  @csrf

                  @if($rule->tipo == 'nat')
                    <input type="hidden" name="acao" value="atualizarNat">
                    <input type="hidden" name="associated-rule-id" value="{{ $rule->{'associated-rule-id'} ?? '' }}">
                  @else
                    <input type="hidden" name="acao" value="atualizarFilter">
                    <input type="hidden" name="tracker" value="{{ $rule->tracker ?? '' }}">
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
