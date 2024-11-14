<?php

use App\Services\Pfsense;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
 */

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');


/**
 * Ao atualizar o código do script, tem de atualizar os remotos. 
 * 
 * Ou fazer uma detecção automatica para atualizar.
 * ou a cada update roda o atualizar remoto para garantir.
 * ainda tem o caso de HA que precisa atualizar no secundário.
 */
Artisan::command('atualizarRemotos', function () {

    // primeiro vamos verificar o status
    $status = Pfsense::status();
    if (!$status['status']) {
        echo 'Status: ', $status['msg'], PHP_EOL;
        echo Pfsense::showLastLog();
        die();
    }

    // depois vamos copiar o arquivo
    if (Pfsense::copiaPlaybackParaRemoto()) {
        echo 'Remoto atualizado: ', config('firewall.ssh') . PHP_EOL;
    } else {
        echo 'Algo deu errado!' . PHP_EOL;
        echo Pfsense::showLastLog();
    }
})->purpose('Modifica e copia o arquivo para pfsense');
