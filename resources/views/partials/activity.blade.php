<div class="card mt-3">
  <div class="card-header h4 mb-3">Registro de atividades</div>
  <div class="card-body">
    <table class="table datatable-simples responsive table-stripped table-sm table-bordered table-hover mb-3">
      <thead>
        <tr>
          <th>Data</th>
          <th>Registro</th>
          <th>IP</th>
          <th>Descrição</th>
        </tr>
      </thead>
      @forelse($activities as $activity)
        <tr>
          <td>{{ $activity->created_at->format('d/m/Y H:i') }}</td>
          <td>{{ $activity->description }}</td>
          <td>{{ $activity->getExtraProperty('agent')['ip'] ?? '' }}</td>
          <td>{{ App\Services\Pfsense::tratarDescricao($activity->getExtraProperty('descr'))[2] }}</td>
        </tr>
      @empty
      @endforelse
    </table>
  </div>
</div>
