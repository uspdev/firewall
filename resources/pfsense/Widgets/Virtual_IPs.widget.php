<?php
/**
 * Este widget mostra os IPs vistuais configurados no pfsense
 * 
 * https://github.com/uspdev/firewall
 * 
 * @author Masaki K, criado em 14/12/2024
 */

// Verifica se o ambiente pfSense está carregado
require_once 'globals.inc';
require_once 'guiconfig.inc';
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
    $status = '<i class="fa fa-' . $icon . '"></i> CARP ' . htmlspecialchars($status);
  } else {
    $status = strtoupper($vip['mode']);
  }
  return $status;
}

// Verifica se o serviço CARP está ativo
$carp_status = get_carp_status();
?>

<div id="virtual-ips-content">
  <?php echo ($carp_status == true) ? '' : '<p>CARP está desativado</p>' ?>
  <?php
  // Obtém todos os VIPs configurados e verifica o status do CARP para cada um
  $vips = &$config['virtualip']['vip'];
  if (!empty($vips)) {
  ?>
    <table class='table table-striped'>
      <thead>
        <tr>
          <th>Endereço</th>
          <th>Interface</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($vips as $vip) { ?>
          <tr>
            <td><?= $vip['subnet'] ?></td>
            <td><?= retornar_interface($vip) ?></td>
            <td><?= retornar_status($vip) ?></td>
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
<?php } else {
    echo "<p>Nenhum endereço CARP configurado.</p>";
  }
?>

<script type="text/javascript">
  // Define o intervalo de atualização em milissegundos
  const refreshInterval = 5000; // 3 segundos
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
        tmp = tmp.querySelector('#virtual-ips-content').innerHTML;

        // Atualiza o conteúdo do widget
        document.getElementById("virtual-ips-content").innerHTML = tmp;

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