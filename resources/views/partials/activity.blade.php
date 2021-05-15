<div class="h4">Registro de atividades</div>
<table class="table table-stripped table-sm table-bordered table-hover">
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
        <td>{{ App\Models\Pfsense::tratarDescricao($activity->getExtraProperty('descr'))[2] }}</td>
    </tr>
    @empty
    @endforelse
</table>
