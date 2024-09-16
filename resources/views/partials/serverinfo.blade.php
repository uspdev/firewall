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
  <h4 class="card-header">Servidor remoto {{ $serverInfo->system->hostname }}.{{ $serverInfo->system->domain }}</h4>
  <div class="card-body">
    <div class="container-fluid" >
      <div class="row">
        <div class="col">
          <div>
            <strong>Versão:</strong>
            span> {{ $serverInfo->version }}</span>
          </div>
          <div>
            <strong>Interfaces:</strong>
          </div>
          @foreach ($serverInfo->interfaces as $interface)
            <div>
              <span>{{ $interface->descr }}: {{ $interface->ipaddr }}</span>
              </div>
          @endforeach
        </div>
        <div class="col">
          <div>
            <strong>CARP:</strong>
          </div>
            @foreach ($serverInfo->virtualip->vip as $carp)
              @if ($carp->mode == "carp")
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
