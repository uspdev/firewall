<?php

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


// esse codigo foi passado para a classe Pfsense e é executado automaticamente quando necessário
// então possivelmente poderá ser removido daqui
Artisan::command('atualizarRemotos', function () {
    if (!config('firewall.ssh')) {
        die('Configure no .env a variável pfsense_ssh' . PHP_EOL);
    }
    $path = base_path('resources/pfsense');
    exec('tail -n +2 ' . $path . '/pfsense-config3.php > ' . $path . '/pfsense-config3');
    exec('scp -i ' . storage_path('firewall.private_key') . ' '. $path . '/pfsense-config3 ' . config('firewall.ssh') . ':/etc/phpshellsessions/pfsense-config3');
    exec('rm ' . $path . '/pfsense-config3');
    echo 'Remotos atualizados' . PHP_EOL;

})->purpose('Modifica e copia o arquivo para pfsense');
