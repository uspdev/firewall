<?php
// copiar a partir daqui para o pfsense 
// em /etc/phpshellsessions
// com o nome pfsense-config2


// https://github.com/uspdev/pfsense-config
require_once("globals.inc");
require_once("filter.inc");
require_once("util.inc");
require_once("config.inc");
require_once("functions.inc");
global $config;
global $argv;
parse_config(true);

if (!empty($argv[3]) && ($argv[3] == 'nat' || $argv[3] == 'filter')) {
    $table = $argv[3];
} else {
    die('primeiro parametro é nat ou filter');
}

// decodificando parametros
if (!empty($argv[4])) {
    $param = unserialize(base64_decode($argv[4]));
    $nat = $param['nat'];
    $filter = $param['filter'];

    // chave usada na busca da regra
    $key = $param['key'];
} else {
    die('sem array de dados');
}

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
    foreach ($config['filter']['rule'] as &$value) {
        if ($value[$key] == $filter[$key]) {
            foreach ($filter as $k => $v) {
                $value[$k] = $v;
            }
            echo 'filterOK,';
        }
    }
}

write_config();
send_event("filter reload");

echo 'ok';
