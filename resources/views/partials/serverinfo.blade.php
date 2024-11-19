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
  <div class="card-header h4">
    <span class="text-danger">Servidor:</span>
    <b>{{ $serverInfo->system->hostname }}.{{ $serverInfo->system->domain }} ({{ config('firewall.ssh') }})</b>
  </div>
  <div class="card-body">
    <div class="row">
      <div class="col-3">
        {{-- <div>Nome: <br>
          <strong class="ml-2">{{ $serverInfo->system->hostname }}.{{ $serverInfo->system->domain }}</strong>
        </div> --}}
        <div>Versão: <strong>{{ $serverInfo->version }}</strong></div>
      </div>
      <div class="col-3">
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
      <div class="col-6">
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

        <div>
          <strong>IP Alias</strong>
        </div>
        <div class="ml-2">
          @foreach ($serverInfo->virtualip->vip as $carp)
            @if ($carp->mode != 'carp')
              <div>
                <span>{{ $carp->descr }} ({{ $carp->mode }}):</span>
                <span>{{ $carp->subnet }}</span>
              </div>
            @endif
          @endforeach
        </div>
      </div>
    </div>
  </div>
</div>
