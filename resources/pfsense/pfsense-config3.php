<?php
/**
 * Este arquivo faz parte do projeto firewall
 * https://github.com/uspdev/firewall
 *
 * Este arquivo sera/foi copiado para o pfsense via ssh
 * na pasta /etc/phpshellsessions/
 * - Este arquivo nao pode conter acentos
 * - a linha 1 que indica a tag php sera/foi removido na copia
 * - como chamar: ssh user@pfsense_ip pfSsh.php playback <acao> [param]
 */

#require_once "globals.inc";
#require_once "filter.inc";
#require_once "util.inc";
#require_once "config.inc";
#require_once "functions.inc";
global $config;
global $argv;
//parse_config(true);

if (empty($argv[3])) {
    die('sem acao');
}
$acao = $argv[3];

if (!in_array($acao, ['nat', 'filter', 'config'])) {
    die('ação invalida: ' . $acao);
}

if ($acao == 'config') {
    echo json_encode($config);
    exit;
}

if (empty($argv[4])) {
    die('sem array de dados param');
}

# decodificando param
$param = unserialize(base64_decode($argv[4]));
$nat = $param['nat'];
$filter = $param['filter'];

// chave usada na busca da regra
$key = $param['key'];

if ($nat) {
    // se for uma regra de nat
    foreach ($config['nat']['rule'] as &$value) {
        if ($value[$key] == $nat[$key]) {
            foreach ($nat as $k => $v) {
                $value[$k] = $v;
            }
            echo 'natOK,';
        }
    }
}

if ($filter) {
    // se for regra de filter
    // para nat também tem regra de filter associada
    foreach ($config['filter']['rule'] as &$value) {
        if ($value[$key] == $filter[$key]) {
            foreach ($filter as $k => $v) {
                $value[$k] = $v;
            }
            echo 'filterOK,';
        }
    }
}

write_config('uspdev-firewall config update');
send_event('filter reload');

echo 'ok';
