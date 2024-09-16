@extends('layouts.app')

@section('content')
@can('admin')
  <h1>Instruções para configurar o servidor pfsense</h1>
  <h6>{{ $msg }}</h6>
  <h5>O servidor não está configurado corretamente.</h5>
  <h5>Abaixo estão instruções possíveis de como solucionar o devido erro:</h5>
  <hr>
  <h6>(Lembrete: a comunicação ssh deve sempre ser feita utilizando uma chave)</h6>
  <div class="card">
    <div class="card-body">
      <h3>Erro no arquivo de ambiente (.env)</h3>
      <p>Se o erro está no arquivo de ambiente .env, o endereço do servidor fornecido nesse
      arquivo provavelmente tem algo de errado.</p>
      <p>Verifique o endereço novamente e garanta que esteja no formato user@ip-do-pfsense.</p>
    </div>
  </div>
  <br>
  <div class="card">
    <div class="card-body">
      <h3>Erro de Host Down</h3>
      <p>Se o endereço está correto, o ip que o firewall está tentando não está funcionando.</p>
      <p>Verifique o status e a estabilidade do host indicado no .env</p>
    </div>
  </div>
  <br>
  <div class="card">
    <div class="card-body">
      <h3>Erro de SSH</h3>
      <p>O endereço que o firewall está tentando acessar funciona, mas não está sendo possível estabelecer uma conexão.</p>
      <p>Verifique o sistema do host e o usuário, indicado no .env</p>
    </div>
  </div>
@endcan
@endsection