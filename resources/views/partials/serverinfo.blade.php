@section('styles')
  @parent
  <style>
    #card-server {
      border: 1px solid #FF0000;
      border-top: 3px solid #FF0000;
    }
  </style>
@endsection

<div class="card" id="card-server">
  <h4 class="card-header">
    <span class="text-danger">Servidor remoto</span>
    <b>{{ $serverInfo->system->hostname }}.{{ $serverInfo->system->domain }}</b>
  </h4>
  <div class="card-body">
    <div class="row">
      <div class="col">
        <div>Nome: <strong>{{ $serverInfo->system->hostname }}.{{ $serverInfo->system->domain }}</strong></div>
        <div>Versão: <strong>{{ $serverInfo->version }}</strong></div>
      </div>
      <div class="col">
        <div>
          <strong>Interfaces</strong>
        </div>
        <div class="ml-2">
          @foreach ($serverInfo->interfaces as $interface)
            <div>
              <span>{{ $interface->descr }}: {{ $interface->ipaddr }}</span>
            </div>
          @endforeach
        </div>
      </div>
      <div class="col">
        <div>
          <strong>CARP</strong>
        </div>
        <div class="ml-2">
          @foreach ($serverInfo->virtualip->vip as $carp)
            @if ($carp->mode == 'carp')
              <div>
                <span>{{ $carp->descr }}:</span>
                <span>{{ $carp->subnet }}</span>
              </div>
            @endif
          @endforeach
        </div>
      </div>
    </div>
  </div>
</div>
