<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use \Carbon\Carbon;
use Illuminate\Support\Str;

class Pfsense extends Model
{
    use HasFactory;

    // protected $ssh_params = sprintf(
    //     '-i %s  -o UserKnownHostsFile=%s -o StrictHostKeyChecking=no -o BatchMode=yes -o ConnectTimeout=2',
    //     config('firewall.private_key'),
    //     storage_path('app/known_hosts')
    // );

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
        $comando = sprintf('ping -c 1 -W 1 %s', $enderecoIP);

        exec($comando, $saida, $codigoDeRetorno);

        if ($codigoDeRetorno === 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Retorna o o status obtido com a tentativa de conexão ao servidor fornecido
     * 
     * True: Conexão estabelecida
     * False: Erro de SSH
     * 
     * @return Integer
     */
    protected static function testarConectividade($ssh)
    {
        $exec_string = sprintf(
            "ssh -tt -F /dev/null -i %s -o UserKnownHostsFile=%s -o StrictHostKeyChecking=no -o BatchMode=yes -o ConnectTimeout=2 %s true 2>&1",
            config('firewall.private_key'),
            storage_path('app/known_hosts'),
            $ssh
        );

        exec($exec_string, $output, $return_var);

        if ($return_var === 0) {
            return true;
        } else {
            return false;
        }
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
        $resultado['status'] = true;
        if (empty($ssh)) {
            $resultado = [
                'status' => false,
                'msg'    => '.env error'
            ];
        } elseif (!Self::testarIP(Str::after($ssh, '@'))) {
            $resultado = [
                'status' => false,
                'msg'    => 'Host Down'
            ];
        } else {
            if (!Self::testarConectividade($ssh)) {
                $resultado = [
                    'status' => false,
                    'msg'    => 'SSH Error'
                ];
            }
        }
        return $resultado;
    }

    /**
     * Lista todas as regras ou por codpes
     */
    public static function ListarRegras(String $codpes = null)
    {
        $config = SELF::obterConfig(true);
        // if (!isset($config->nat->rules)) {
        //     return collect();
        // }
        if ($codpes) {
            $rules = SELF::listarNat($codpes);
            $rules = $rules->merge(SELF::listarFilter($codpes));
        } else {
            if (!empty($config->nat->rule)) {
                $rules = collect($config->nat->rule);
                foreach ($rules as &$rule) {
                    // vamos separar a descrição nas suas partes [codpes,data,descrição]
                    list($rule->codpes, $rule->data, $rule->descttd) = SELF::tratarDescricao($rule->descr);
                }
            } else {
                $rules = collect();
            }
        }
        return $rules;
    }

    /**
     * lista as regras de nat para um usuário
     */
    protected static function listarNat(string $codpes, $formatado = true)
    {
        $out = collect();

        $config = SELF::obterConfig();
        if (!isset($config->nat->rules)) {
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
            'ssh -i %s  -o UserKnownHostsFile=%s -o StrictHostKeyChecking=no -o BatchMode=yes -o ConnectTimeout=2 %s pfSsh.php playback %s %s %s', // 2>&1
            config('firewall.private_key'),
            storage_path('app/known_hosts'),
            config('firewall.ssh'),
            $remote_script,
            $acao,
            base64_encode(serialize($param))
        );
        exec($exec_string, $exec_return, $exec_code);

        return $exec_return;
    }

    protected static function toObj($arr)
    {
        return json_decode(json_encode($arr));
    }

    protected static function objToArray($obj)
    {
        return json_decode(json_encode($obj), true);
    }
}
