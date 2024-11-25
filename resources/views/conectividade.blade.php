@extends('layouts.app')

@section('content')
  @can('admin')
    <div class="alert alert-danger">{{ $msg }}</div>

    <div>O servidor não está acessível ou não está configurado corretamente!</div>
    <div>
      FIREWALL_SSH: {{ config('firewall.ssh') }}<br>
      PRIVATE_KEY: {{ config('firewall.private_key') }}
    </div>
    <hr>
    <h6>(Lembrete: a comunicação ssh deve sempre ser feita utilizando uma chave)</h6>
    <div class="card">
      <div class="card-body py-2">
        <div class="h5">Erro no arquivo de ambiente (.env)</div>
        Se o erro está no arquivo de ambiente .env, o endereço do servidor fornecido nesse
        arquivo provavelmente tem algo de errado.<br>
        Verifique o endereço novamente e garanta que esteja no formato user@ip-do-pfsense.
      </div>
    </div>
    <br>
    <div class="card">
      <div class="card-body py-2">
        <div class="h5">Erro de Host Down</div>
        Se o endereço está correto, o ip que o firewall está tentando não está funcionando.<br>
        Verifique o status e a estabilidade do host indicado no .env
      </div>
    </div>
    <br>
    <div class="card">
      <div class="card-body py-2">
        <div class="h5">Erro de SSH</div>
        O endereço que o firewall está tentando acessar funciona, mas não está sendo possível estabelecer uma conexão.<br>
        Verifique o sistema do host e o usuário, indicado no .env
      </div>
    </div>
  @endcan
@endsection
