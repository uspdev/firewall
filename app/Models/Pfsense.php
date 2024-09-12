<?php

namespace App\Models;

use \Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Pfsense extends Model
{
    use HasFactory;

    /**
     * Retorna parametros comuns para o ssh
     * 
     * -tt: orça alocar terminal
     * -F /dev/null: indica null para config file
     * -i: caminho para private key
     */
    protected static function sshParams()
    {
        return sprintf(
            '-F /dev/null -i %s -o UserKnownHostsFile=%s -o StrictHostKeyChecking=no -o BatchMode=yes -o ConnectTimeout=2',
            config('firewall.private_key'),
            storage_path('app/known_hosts')
        );
    }

    /**
     * Retorna o status da comunicação com o pfsense
     * 
     * True: Tudo certo
     * Erro no .env
     * Host down
     * Erro de SSH
     * Erro no pfsense - não implementado
     * 
     * @return Array [status => true|false, msg => '']
     */
    public static function status()
    {
        $ssh = config('firewall.ssh');

        if (empty($ssh)) {
            return ['status' => false, 'msg' => '.env error'];
        }

        if (!Self::testarIP(Str::after($ssh, '@'))) {
            return ['status' => false, 'msg' => 'Host Down'];
        }

        if (!Self::testarConectividade()) {
            return ['status' => false, 'msg' => 'SSH Error'];
        }

        return ['status' => true, 'msg' => ''];
    }

    /**
     * Retorna o código de retorno da conexão com o IP fornecido
     * 
     * True: Conexão pode ser estabelecida
     * False: Host down
     * 
     * @return Boolean
     */
    protected static function testarIP($enderecoIP)
    {
        $exec_string = sprintf('ping -c 1 -W 1 %s', $enderecoIP);
        exec($exec_string, $exec_output, $exec_code);

        return ($exec_code === 0)  ? true : false;
    }

    /**
     * Retorna o o status obtido com a tentativa de conexão ssh ao servidor fornecido
     * 
     * True: Conexão estabelecida
     * False: Erro de SSH
     * 
     * @return Integer
     */
    protected static function testarConectividade()
    {
        $exec_string = sprintf('ssh %s %s true 2>&1', self::sshParams(), config('firewall.ssh'));
        exec($exec_string, $exec_output, $exec_code);

        if ($exec_code === 0) {
            return true;
        } else {
            self::log($exec_string, $exec_output, $exec_code);
            return false;
        }
    }

    /**
     * Grava em log especifico do pfsense a variável $msg
     */
    protected static function log(...$msg)
    {
        Log::build([
            'driver' => 'single',
            'path' => storage_path('logs/pfsense.log'),
            'permission' => 0664,
        ])->info(json_encode($msg));

        return true;
    }

    /**
     * Mostra o ultimo registro de log
     */
    public static function showLastLog()
    {
        $file = escapeshellarg(storage_path("logs/pfsense.log"));
        return `tail -n 1 $file`;
    }

    /**
     * Copia o arquivo de playback para o pfsense
     */
    public static function copiaPlaybackParaRemoto()
    {
        $path = base_path('resources/pfsense');

        // remove a 1a. linha do arquivo pois não deve existir para executar corretamente no pfsense
        exec('tail -n +2 ' . $path . '/pfsense-config3.php > ' . $path . '/pfsense-config3');

        // copia o arquivo
        $exec_string = strtr(
            'scp {params} {src} {host}:/etc/phpshellsessions/pfsense-config3 2>&1',
            [
                '{params}' => self::sshParams(),
                '{src}' => $path . '/pfsense-config3',
                '{host}' => config('firewall.ssh')
            ]
        );
        exec($exec_string, $return, $code);

        // remove o arquivo temporario
        exec('rm ' . $path . '/pfsense-config3');

        self::log('Copiou playback: ' . $exec_string, $return, $code);
        return ($code === 0) ? true : false;
    }

    /**
     * Lista todas as regras ou por codpes
     */
    public static function ListarRegras(int $codpes = null)
    {
        $config = SELF::obterConfig(true);
        if ($codpes) {
            $rules = SELF::listarNat($codpes);
            return $rules->merge(SELF::listarFilter($codpes));
        }
        $rules = collect();
        if (!empty($config->nat->rule)) {
            foreach ($config->nat->rule as &$rule) {
                // vamos separar a descrição nas suas partes [codpes,data,descrição]
                list($rule->codpes, $rule->data, $rule->descttd) = SELF::tratarDescricao($rule->descr);
                $rule->tipo = 'nat'; 
                $rules->push($rule); 
            }
        }

        if (!empty($config->filter->rule)) {
            foreach ($config->filter->rule as &$rule) {
                list($rule->codpes, $rule->data, $rule->descttd) = SELF::tratarDescricao($rule->descr);
                if (empty($rule->destination->address)) { 
                    $rule->destination->address = $rule->interface; 
                }
                $rule->tipo = 'filter'; 
                if (!isset($rule->{'associated-rule-id'})) { 
                    $rules->push($rule); 
                }
            }
        }
        return $rules;
    }

    /**
     * lista as regras de nat para um usuário
     */
    protected static function listarNat(int $codpes, $formatado = true)
    {
        $out = collect();

        $config = SELF::obterConfig();
        if (!isset($config->nat->rule)) {
            return collect();
        }

        foreach (SELF::obterConfig()->nat->rule as $rule) {

            // procura o codpes na descricao
            if (strpos($rule->descr, $codpes) !== false) {

                # vamos formatar os dados para exibição
                if ($formatado) {
                    list($rule->codpes, $rule->data, $rule->descttd) = SELF::tratarDescricao($rule->descr);
                    $rule->tipo = 'nat';
                }
                $out->push($rule);
            }
        }
        return $out;
    }

    /**
     * Lista as regras de filter para um usuário
     */
    protected static function listarFilter(string $codpes, $formatado = true, $automaticos = false)
    {
        $out = collect();

        $config = SELF::obterConfig();
        if (!isset($config->filter->rule)) {
            return collect();
        }

        foreach (SELF::obterConfig()->filter->rule as $rule) {

            if ($automaticos) {
                # procura o codpes na descrição
                $condicao = strpos($rule->descr, $codpes) !== false;
            } else {
                # procura o codpes na descricao e exclui os automáticos (automáticos começam com 'NAT ')
                $condicao = strpos($rule->descr, $codpes) !== false && strpos($rule->descr, 'NAT ') !== 0;
            }

            if ($condicao) {
                if ($formatado) {
                    list($rule->codpes, $rule->data, $rule->descttd) = SELF::tratarDescricao($rule->descr);
                    if (empty($rule->destination->address)) {
                        $rule->destination->address = $rule->interface;
                    }
                    $rule->tipo = 'filter';
                }

                $out->push($rule);
            }
        }
        return $out;
    }

    /**
     * Atualiza regras de NAT
     */
    public static function atualizarNat($user, $associated_rule_id)
    {
        foreach (SELF::listarNat($user->codpes, false) as $nat) {
            if (isset($nat->{'associated-rule-id'}) && $nat->{'associated-rule-id'} == $associated_rule_id) {
                $nat->source->address = $user->ip;
                $nat->descr = preg_replace("/\(.*?\)/", "(" . date('Y-m-d') . ")", $nat->descr);
                $param['nat'] = SELF::objToArray($nat);
                break; # ao encontrar vamos parar pois tem somente um
            }
        }

        # tem de atualizar também a regra de filter associada ao NAT
        foreach (SELF::listarFilter($user->codpes, false, true) as $filter) {
            if (isset($filter->{'associated-rule-id'}) && $filter->{'associated-rule-id'} == $associated_rule_id) {
                $filter->descr = preg_replace("/\(.*?\)/", "(" . date('Y-m-d') . ")", $filter->descr);
                $filter->source->address = $user->ip;
                $param['filter'] = SELF::objToArray($filter);
                break; # ao encontrar vamos parar pois tem somente um
            }
        }

        $param['key'] = 'associated-rule-id'; # chave para busca no caso de nat
        $exec_return = SELF::aplicarAtualizacao('nat', $param);
        SELF::obterConfig(true);
        activity()->causedBy($user)->withProperties(['descr' => $nat->descr, 'exec' => $exec_return])->log('Regra nat atualizada');
    }

    /**
     * Atualiza regras filter que não são NAT
     */
    public static function atualizarFilter($user, $tracker)
    {
        foreach (SELF::listarFilter($user->codpes, false) as $filter) {
            if ($filter->tracker == $tracker) {
                $filter->descr = preg_replace("/\(.*?\)/", "(" . date('Y-m-d') . ")", $filter->descr);
                $filter->source->address = $user->ip;
                $param['filter'] = SELF::objToArray($filter);
                break; # ao encontrar vamos parar pois tem somente um
            }
        }

        $param['key'] = 'tracker'; # chave para busca no caso de filter
        $exec_return = SELF::aplicarAtualizacao('filter', $param);
        SELF::obterConfig(true);
        activity()->causedBy($user)->withProperties(['descr' => $filter->descr, 'exec' => $exec_return])->log('Regra filter atualizada');
    }

    /**
     * Obtém o config do firewall remoto
     *
     * Guarda na sessão, atualiza a sessão caso $atualizar = true
     * 
     * TODO: talvez poderia guardar no cache do laravel, assim compartilha com todos os usuários
     */
    public static function obterConfig($atualizar = false)
    {
        if ($atualizar || empty($_SESSION['pf_config'])) {
            $pf_config = SELF::aplicarAtualizacao('config');
            if (empty($pf_config)) {
                $pf_config[0] = null;
            }
            $_SESSION['pf_config'] = json_decode($pf_config[0], true);
        }

        return SELF::toObj($_SESSION['pf_config']);
    }

    /**
     * Separa descrição em suas partes [codpes, data, descrição]
     */
    public static function tratarDescricao($descr)
    {
        $descttd = \preg_split('/\s?\(|\)\s?/', $descr);
        if (count($descttd) == 3) {
            $descttd[1] = $descttd[1] ? Carbon::parse($descttd[1]) : '';
            return $descttd;
        } else {
            return ['', '', null];
        }
    }

    /**
     * Aplica as regras no firewall remoto e obtém as configurações atualizadas
     */
    protected static function aplicarAtualizacao($acao, $param = [])
    {
        $remote_script = 'pfsense-config3';

        $exec_string = sprintf(
            'ssh %s %s pfSsh.php playback %s %s %s', // 2>&1
            self::sshParams(),
            config('firewall.ssh'),
            $remote_script,
            $acao,
            base64_encode(serialize($param))
        );
        exec($exec_string, $exec_return, $exec_code);

        // vamos copiar o script remoto se necessário e rodar novamente
        if (isset($exec_return[0]) && $exec_return[0] == 'Error: Invalid playback file specified.') {
            self::copiaPlaybackParaRemoto();
            unset($exec_return);
            exec($exec_string, $exec_return, $exec_code); //roda novamente
        }

        return $exec_return;
    }

    /**
     * Converte array para objeto
     * 
     * Deveria esta numa classe de utils???
     */
    protected static function toObj(array $arr)
    {
        return json_decode(json_encode($arr));
    }

    /**
     * Converte objeto para Array
     */
    protected static function objToArray($obj)
    {
        return json_decode(json_encode($obj), true);
    }
}
