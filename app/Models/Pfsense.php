<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pfsense extends Model
{
    use HasFactory;
    const remote_script = 'pfsense-config2';

    /**
     * Lista todas as regras
     */
    public static function ListarRegras()
    {
        $config = SELF::obterConfig(true);
        $rules = Pfsense::toObj($config['nat']['rule']);
        foreach ($rules as &$rule) {
            // vamos separar a descrição nas suas partes [codpes,data,descrição]
            list($rule->codpes, $rule->data, $rule->descttd) = SELF::tratarDescricao($rule->descr);
        }
        return $rules;
    }

    /**
     * Separa descrição em suas partes [codpes,data,descrição]
     */
    public static function tratarDescricao($descr)
    {
        $descttd = \preg_split('/\s?\(|\)\s?/', $descr);
        if (count($descttd) == 3) {
            return $descttd;
        } else {
            return ['', '', null];
        }
    }

    /** 
     * lista as regras de nat para um usuário
     */
    public static function listarNat(string $codpes)
    {
        $config = SELF::obterConfig();

        $out = array();
        foreach ($config['nat']['rule'] as $rule) {
            // procura o codpes na descricao
            if (strpos($rule['descr'], $codpes) !== false) {
                //SELF::replaceDash($rule);
                $rule['tipo'] = 'nat';
                array_push($out, $rule);
            }
        }
        return SELF::toObj($out);
    }

    public static function listarFilter(string $codpes)
    {
        $config = SELF::obterConfig();

        $out = [];
        foreach ($config['filter']['rule'] as &$rule) {
            // procura o codpes na descricao e exclui os automáticos
            if (strpos($rule['descr'], $codpes) !== false && strpos($rule['descr'], 'NAT ') !== 0) {
                SELF::replaceDash($rule);
                if (empty($rule['destination']['address'])) {
                    $rule['destination']['address'] = $rule['interface'];
                }
                $rule['tipo'] = 'filter';
                array_push($out, $rule);
            }
        }
        return SELF::toObj($out);
    }

    public static function atualizarNat($usr, $associated_rule_id)
    {
        $log = array();
        $log['ts'] = date('Y-m-d H:i:s');
        $log['codpes'] = $usr->codpes;
        $log['name'] = $usr->nome;

        //echo $associated_rule_id;exit;
        foreach (SELF::listarNat($usr->codpes) as $nat) {
            if ($nat->associated_rule_id == $associated_rule_id) {
                $log['target'] = $nat->destination->address . ':' . $nat->destination->port;
                $log['prev_ip'] = $nat->source->address;
                $log['new_ip'] = $usr->ip;

                $nat->source->address = $usr->ip;
                $nat->descr = preg_replace("/\(.*?\)/", "(" . date('Y-m-d') . ")", $nat->descr);

                $nat = SELF::objToArray($nat);
                SELF::replaceUnderscore($nat);
                $param['nat'] = $nat;
                break;
            }
        }

        foreach (SELF::listarFilter($usr->codpes) as $filter) {
            if (!empty($filter->associated_rule_id) && $filter->associated_rule_id == $associated_rule_id) {
                $filter->source->address = $usr->ip;
                $filter->descr = preg_replace("/\(.*?\)/", "(" . date('Y-m-d') . ")", $filter->descr);

                $filter = SELF::objToArray($filter);
                SELF::replaceUnderscore($filter);
                $param['filter'] = $filter;
                break;
            }
        }

        // chave para busca no caso de nat
        $param['key'] = 'associated-rule-id';

        $exec_string = sprintf(
            'ssh %s pfSsh.php playback %s nat %s',
            $_ENV['pfsense_ssh'],
            SELF::remote_script,
            base64_encode(serialize($param))
        );
        echo json_encode($param);exit;
        exec($exec_string, $fw);
        //Log::update($log);

        // recarrega a configuração atualizada
        SELF::obterConfig(true);
    }

    public static function atualizarFilter($usr, $descr)
    {
        $log = array();
        $log['ts'] = date('Y-m-d H:i:s');
        $log['codpes'] = $usr->codpes;
        $log['name'] = $usr->nome;

        foreach (SELF::listarFilter($usr->codpes) as $filter) {
            if ($filter->descr == $descr) {
                $log['target'] = $filter->destination->address;
                $log['prev_ip'] = $filter->source->address;
                $log['new_ip'] = $usr->ip;

                $filter->descr = preg_replace("/\(.*?\)/", "(" . date('Y-m-d') . ")", $filter->descr);
                $filter->source->address = $usr->ip;

                $filter = SELF::objToArray($filter);
                SELF::replaceUnderscore($filter);
                $param['filter'] = $filter;
                break;
            }
        }

        // chave para busca no caso de filter
        $param['key'] = 'tracker';

        $exec_string = sprintf(
            'ssh %s pfSsh.php playback %s filter %s',
            $_ENV['pfsense_ssh'],
            SELF::remote_script,
            base64_encode(serialize($param))
        );
        exec($exec_string, $fw);
        //Log::update($log);

        // recarrega a configuração atualizada
        SELF::obterConfig(true);
    }

    public static function obterConfig($atualizar = false)
    {
        if ($atualizar || empty($_SESSION['pf_config'])) {
            exec('ssh ' . $_ENV['pfsense_ssh'] . ' pfSsh.php playback pc-getConfig', $pf_config);
            $_SESSION['pf_config'] = json_decode($pf_config[0], true);
        }

        return $_SESSION['pf_config'];
    }

    protected static function replaceDash(&$array)
    {
        $array = array_combine(
            array_map(
                function ($str) {
                    return str_replace("-", "_", $str);
                },
                array_keys($array)
            ),
            array_values($array)
        );
    }

    protected static function replaceUnderscore(&$array)
    {
        $array = array_combine(
            array_map(
                function ($str) {
                    return str_replace("_", "-", $str);
                },
                array_keys($array)
            ),
            array_values($array)
        );
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
