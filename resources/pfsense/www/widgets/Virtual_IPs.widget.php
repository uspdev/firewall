<?php

/**
 * Este widget mostra os IPs vistuais configurados no pfsense
 * 
 * sudo php artisan atualizarRemotos
 * 
 * Diagnostics->Edit File
 *   /usr/local/www/widgets/widgets/Virtual_IPs.widget.php
 * SAVE
 * BROWSE
 *   copy->paste
 * SAVE
 * DASHBOARD-> ADD -> Virtual IP
 * 
 * versão 1, em 14/12/2024
 * 
 * https://github.com/uspdev/firewall
 * 
 * @author Masaki K, criado em 14/12/2024
 */

// Verifica se o ambiente pfSense está carregado
// require_once 'globals.inc';
require_once 'guiconfig.inc';
require_once 'pfsense-utils.inc';
require_once 'functions.inc';
require_once '/usr/local/www/widgets/include/carp_status.inc';

function retornar_interface($vip)
{
  global $config;

  if (substr($vip['interface'], 0, 4) == '_vip') {
    // Verifica todos os VIPs configurados
    foreach ($config['virtualip']['vip'] as $svip) {
      // Verifica se o VIP possui o uniqid especificado
      if ($svip['uniqid'] === substr($vip['interface'], 4)) {
        return $svip['interface'] . '@' . $svip['vhid'];
      }
    }
  } else {
    // Retorna null se o uniqid não foi encontrado
    return $vip['interface'] . '@' . $vip['vhid'];
  }
}

function retornar_carp_status($vip)
{
  $status = strtoupper(get_carp_interface_status("_vip{$vip['uniqid']}"));
  if ($status) {
    $carp_enabled = get_carp_status();
    if ($carp_enabled == false) {
      $icon = 'times-circle';
      $status = "DISABLED";
    } else {
      if ($status == "MASTER") {
        $icon = 'play-circle text-success';
      } else if ($status == "BACKUP") {
        $icon = 'pause-circle text-warning';
      } else if ($status == "INIT") {
        $icon = 'question-circle text-danger';
      }
    }
    $status = '<i class="fa fa-' . $icon . '"></i> ' . htmlspecialchars($status);
  } else {
    $status = strtoupper($vip['mode']);
  }
  return $status;
}

function retornar_status($vip)
{
  $status = strtoupper(get_carp_interface_status("_vip{$vip['uniqid']}"));
  if ($status) {
    $carp_enabled = get_carp_status();
    if ($carp_enabled == false) {
      $icon = 'times-circle';
      $status = "DISABLED";
    } else {
      if ($status == "MASTER") {
        $icon = 'play-circle text-success';
      } else if ($status == "BACKUP") {
        $icon = 'pause-circle text-warning';
      } else if ($status == "INIT") {
        $icon = 'question-circle text-danger';
      }
    }
    $status = '<i class="fa fa-' . $icon . '"></i> ' . htmlspecialchars($status);
  } else {
    $status = strtoupper($vip['mode']);
  }
  return $status;
}

/**
 * Ordena o arrar $arr pela chave 'ipaddress' do subarray
 */
function ordenar_por_ip(&$arr)
{
  return usort($arr, function ($a, $b) {
    $ipA = array_map('intval', explode('.', $a['ipaddress']));
    $ipB = array_map('intval', explode('.', $b['ipaddress']));
    // Comparar octeto por octeto
    for ($i = 0; $i < 4; $i++) {
      if ($ipA[$i] < $ipB[$i]) {
        return -1;
      }
      if ($ipA[$i] > $ipB[$i]) {
        return 1;
      }
    }
    return 0; // São iguais
  });
}

function listar_interfaces_carp()
{
  $ret = [];
  $carpint = 0;
  foreach (config_get_path('virtualip/vip', []) as $carp) {
    if ($carp['mode'] != "carp") {
      continue;
    }
    $carpint++;
    $c['ipaddress'] = $carp['subnet']; //tabela
    $c['status'] = get_carp_interface_status("_vip{$carp['uniqid']}");

    $c['interface_title'] = htmlspecialchars($carp['descr']);
    $c['interface'] = htmlspecialchars(convert_friendly_interface_to_friendly_descr($carp['interface']) . "@" . $carp['vhid']);
    if (get_carp_status() == false) {
      $c['status'] = '<i class="fa fa-times-circle"></i> DISABLED';
    } else {
      if ($c['status'] == "MASTER") {
        $c['status'] = '<i class="fa fa-play-circle text-success"></i> master';
      } else if ($c['status'] == "BACKUP") {
        $c['status'] = '<i class="fa fa-pause-circle text-warning"></i> backup';
      } else if ($c['status'] == "INIT") {
        $c['status'] = '<i class="fa fa-question-circle text-danger"></i> init';
      }
    }
    $ret[] = $c;
  }
  return $ret;
}

function listar_ip_alias()
{
  global $config;
  $ret = [];
  foreach (config_get_path('virtualip/vip') as $vip) {
    if ($vip['mode'] != 'ipalias') {
      continue;
    }
    // print_r($vip);
    $c['ipaddress'] = $vip['subnet'];
    $c['status'] = '<i class="far fa-star"></i> VIP';
    $c['interface_title'] = $vip['descr'];
    // Verifica todos os VIPs configurados
    foreach ($config['virtualip']['vip'] as $svip) {
      // Verifica se o VIP possui o uniqid especificado
      if ($svip['uniqid'] === substr($vip['interface'], 4)) {
        $c['interface'] =  strtoupper($svip['interface']) . '@' . $svip['vhid'];
        break;
      }
    }
    $ret[] = $c;
  }
  return $ret;
}

function listarInterfaces()
{
  $ret = [];
  foreach (config_get_path('interfaces') as $ifdescr => $iface) {
    $i['ipaddress'] = $iface['ipaddr'];
    $i['interface'] = '<i class="fa fa-sitemap"></i> ' . $iface['descr'];

    // Choose an icon by interface status
    $ifinfo = get_interface_info($ifdescr);
    if ($ifinfo['status'] == "up" || $ifinfo['status'] == "associated") {
      $i['status'] = '<i class="fa fa-arrow-up text-success"></i> ' . $ifinfo['if'];
    } elseif ($ifinfo['status'] == "no carrier") {
      $i['status'] = '<i class="fa fa-times-circle text-danger"></i> ' . $ifinfo['if'];
    } elseif ($ifinfo['status'] == "down") {
      $i['status'] = '<i class="fa fa-arrow-down text-danger"></i> ' . $ifinfo['if'];
    } else {
      $i['status'] = '<i class="fa fa-arrow-down text-danger"></i> ' . $ifinfo['if'];
    }
    $i['interface_title'] = 'MAC: ' . $ifinfo['macaddr'] . ', IF: '. $ifdescr;
    $ret[] = $i;
  }
  return $ret;
}

function listar_ips()
{
  $ret = array_merge(listar_interfaces_carp(), listar_ip_alias(), listarInterfaces());
  ordenar_por_ip($ret);
  return $ret;
}

?>
<div id="uspdev-widget">
  <div id="uspdev-carp">
    <table class="table table-striped table-hover">
      <thead>
        <tr>
          <th>IP address</th>
          <th>Interface</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach (listar_ips() as $ip) { ?>
          <tr title="<?= $ip['interface_title'] ?>">
            <td><?= $ip['ipaddress']; ?></td>
            <td><?= $ip['interface']; ?></td>
            <td><?= $ip['status'] ?></td>
          </tr>
        <?php } ?>
        <!-- Linha para a Barra de Progresso -->
        <tr>
          <td colspan="3">
            <div id="progress-bar-container" style="width: 20%; background-color: #ddd;">
              <div id="progress-bar" style="width: 0%; height: 5px; background-color: #4CAF50;"></div>
            </div>
          </td>
        </tr>
      </tbody>
    </table>
  </div>

</div>

<?php
/* for AJAX response, we only need the panels */
if ($_REQUEST['ajax']) {
  exit;
}
?>

<script type="text/javascript">
  // Define o intervalo de atualização em milissegundos
  const refreshInterval = 10000; // 10 segundos
  let progress = 0;
  let progressBar = document.getElementById("progress-bar");

  // Função para atualizar a barra de progresso
  function updateProgressBar() {
    progress += 100 / (refreshInterval / 100); // Atualiza a barra em passos de 100ms
    progressBar.style.width = progress + "%";

    if (progress >= 100) {
      progress = 0; // Reseta o progresso
      refreshCarpStatus(); // Recarrega o conteúdo quando a barra atinge 100%
    }
  }

  // Função para atualizar o conteúdo do widget
  function refreshCarpStatus() {
    var xhr = new XMLHttpRequest();
    xhr.open("GET", "/widgets/widgets/Virtual_IPs.widget.php", true);
    xhr.onload = function() {
      if (xhr.status === 200) {
        var tmp = document.createElement('div');
        tmp.innerHTML = xhr.responseText;
        tmp = tmp.querySelector('#uspdev-widget').innerHTML;

        // Atualiza o conteúdo do widget
        document.getElementById("uspdev-widget").innerHTML = tmp;

        // Após o conteúdo ser recarregado, encontrar a nova barra de progresso
        progressBar = document.getElementById("progress-bar"); // Atualiza a referência da barra de progresso
        progress = 1; // Reseta o progresso
        progressBar.style.width = progress + "%"; // Reseta a barra de progresso
      }
    };
    xhr.send();
  }

  // Intervalo para atualização automática do conteúdo
  setInterval(updateProgressBar, 100); // Atualiza a barra de progresso a cada 100 ms
</script>