<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use \Carbon\Carbon;

class Pfsense extends Model
{
    use HasFactory;

    /**
     * Lista todas as regras ou por codpes
     */
    public static function ListarRegras(String $codpes = null)
    {
        if ($codpes) {
            $rules = SELF::listarNat($codpes);
            $rules = $rules->merge(SELF::listarFilter($codpes));
        } else {
            $rules = collect(SELF::obterConfig(true)->nat->rule);
            foreach ($rules as &$rule) {
                // vamos separar a descrição nas suas partes [codpes,data,descrição]
                list($rule->codpes, $rule->data, $rule->descttd) = SELF::tratarDescricao($rule->descr);
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
            $_SESSION['pf_config'] = json_decode($pf_config[0], true);
        }

        return SELF::toObj($_SESSION['pf_config']);
    }

    /**
     * Separa descrição em suas partes [codpes, data, descrição]
     */
    protected static function tratarDescricao($descr)
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
            'ssh %s pfSsh.php playback %s %s %s',
            config('firewall.ssh'),
            $remote_script,
            $acao,
            base64_encode(serialize($param))
        );
        exec($exec_string, $exec_return);

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
