<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use \Carbon\Carbon;

class Pfsense extends Model
{
    use HasFactory;
    const remote_script = 'pfsense-config2';

    /**
     * Lista todas as regras ou por codpes
     */
    public static function ListarRegras(String $codpes = null)
    {
        if (!$codpes) {
            $config = SELF::obterConfig(true);
            $rules = collect(SELF::toObj($config['nat']['rule']));
            foreach ($rules as &$rule) {
                // vamos separar a descrição nas suas partes [codpes,data,descrição]
                list($rule->codpes, $rule->data, $rule->descttd) = SELF::tratarDescricao($rule->descr);
            }
        } else {
            $rules = SELF::listarNat($codpes);
            $rules = $rules->merge(SELF::listarFilter($codpes));
        }

        return $rules;
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
     * lista as regras de nat para um usuário
     */
    protected static function listarNat(string $codpes)
    {
        $config = SELF::obterConfig();

        $out = collect();
        foreach ($config['nat']['rule'] as $rule) {
            $rule = SELF::toObj($rule);
            list($rule->codpes, $rule->data, $rule->descttd) = SELF::tratarDescricao($rule->descr);

            // procura o codpes na descricao
            if ($rule->codpes == $codpes) {
                $rule->tipo = 'nat';
                $out->push($rule);
            }
        }
        return $out;
    }

    protected static function listarFilter(string $codpes)
    {
        $config = SELF::obterConfig();
        $out = collect();

        foreach ($config['filter']['rule'] as $rule) {
            $rule = SELF::toObj($rule);
            list($rule->codpes, $rule->data, $rule->descttd) = SELF::tratarDescricao($rule->descr);

            // procura o codpes na descricao e exclui os automáticos
            if ($rule->codpes == $codpes && strpos($rule->descr, 'NAT ') !== 0) {
                if (empty($rule->destination->address)) {
                    $rule->destination->address = $rule->interface;
                }
                $rule->tipo = 'filter';
                $out->push($rule);
            }
        }
        return $out;
        return SELF::toObj($out);
    }

    /**
     * Atualiza regras de NAT
     */
    public static function atualizarNat($user, $associated_rule_id)
    {
        foreach (SELF::listarNat($user->codpes) as $nat) {
            if (isset($nat->{'associated-rule-id'}) && $nat->{'associated-rule-id'} == $associated_rule_id) {
                $nat->source->address = $user->ip;

                # atualizando a data na descr da regra
                $nat->descr = preg_replace("/\(.*?\)/", "(" . date('Y-m-d') . ")", $nat->descr);

                $param['nat'] = SELF::objToArray($nat);
                break;
            }
        }

        foreach (SELF::listarFilter($user->codpes) as $filter) {
            if (!empty($filter->{'associated-rule-id'}) && $filter->{'associated-rule-id'} == $associated_rule_id) {
                $filter->source->address = $user->ip;
                $filter->descr = preg_replace("/\(.*?\)/", "(" . date('Y-m-d') . ")", $filter->descr);

                $param['filter'] = SELF::objToArray($filter);
                break;
            }
        }

        // chave para busca no caso de nat
        $param['key'] = 'associated-rule-id';

        $exec_string = sprintf(
            'ssh %s pfSsh.php playback %s nat %s',
            config('firewall.ssh'),
            SELF::remote_script,
            base64_encode(serialize($param))
        );
        exec($exec_string, $fw);

        activity()->causedBy($user)->withProperties(['descr' => $nat->descr])->log('Regra nat atualizada');

        // recarrega a configuração atualizada
        SELF::obterConfig(true);

        return $fw;
    }

    /**
     * Atualiza regras filter que não são NAT
     */
    public static function atualizarFilter($user, $descr)
    {
        $log = array();
        $log['ts'] = date('Y-m-d H:i:s');
        $log['codpes'] = $user->codpes;
        $log['name'] = $user->nome;

        foreach (SELF::listarFilter($user->codpes) as $filter) {
            if ($filter->descr == $descr) {
                $log['target'] = $filter->destination->address;
                $log['prev_ip'] = $filter->source->address;
                $log['new_ip'] = $user->ip;

                $filter->descr = preg_replace("/\(.*?\)/", "(" . date('Y-m-d') . ")", $filter->descr);
                $filter->source->address = $user->ip;

                $filter = SELF::objToArray($filter);
                $param['filter'] = $filter;
                break;
            }
        }

        // chave para busca no caso de filter
        $param['key'] = 'tracker';

        $exec_string = sprintf(
            'ssh %s pfSsh.php playback %s filter %s',
            config('firewall.ssh'),
            SELF::remote_script,
            base64_encode(serialize($param))
        );
        exec($exec_string, $fw);

        activity()->causedBy($user)->withProperties(['descr' => $descr])->log('Regra filter atualizada');

        // recarrega a configuração atualizada
        SELF::obterConfig(true);
    }

    public static function obterConfig($atualizar = false)
    {
        if ($atualizar || empty($_SESSION['pf_config'])) {
            exec('ssh ' . config('firewall.ssh') . ' pfSsh.php playback pc-getConfig', $pf_config);
            $_SESSION['pf_config'] = json_decode($pf_config[0], true);
        }

        return $_SESSION['pf_config'];
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
