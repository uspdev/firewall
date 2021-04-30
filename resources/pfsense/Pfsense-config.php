<?php
// copiar a partir daqui para o pfsense

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

// proximo parametro é a string de busca
if (!empty($argv[4])) {
    $search = $argv[4];
} else {
    die('sem string de busca');
}
if (!empty($argv[5])) {
    $src_addr = $argv[5];
} else {
    $src_addr = '';
}

// find
$out = array();
foreach ($config[$table]['rule'] as &$value) {
    if ($table == 'nat') {
        $haystack = $value['descr'];
    } else {
        $haystack = $value['tracker'];
    }
    if (strpos($haystack, $search) !== false) {
        if ($src_addr) {
            $value['source']['address'] = $src_addr;
            $value['descr'] = preg_replace("/\(.*?\)/", "(" . date('Y-m-d') . ")", $value['descr']);
            array_push($out, $value);
            //ok
        } else {
            array_push($out, $value);
        }
    }
}
if ($src_addr) {
    write_config();
    send_event("filter reload");
}
echo json_encode($out);
