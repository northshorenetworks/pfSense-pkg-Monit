<?php

  define('MONIT_CONFIG_DIR', '/usr/local/pkg/monit/');
  define('MONIT_STARTUP_SCRIPT', '/usr/local/etc/rc.d/monit.sh');
  
  require_once('config.inc');
  require_once('service-utils.inc');

  global $config, $g;

  if (!is_array($config['installedpackages']['monitglobal']))
    $config['installedpackages']['monitglobal'] = array();

  $monit = &$config['installedpackages']['monitglobal'];

  function monit_write_config() {
    global $config;
    $monit = &$config['installedpackages']['monitglobal'];
	
    $fd = fopen(MONIT_CONFIG_DIR . 'monitrc', 'w');
    fwrite($fd, base64_decode($monit['monit_config']));
    fclose($fd);
    exec(MONIT_STARTUP_SCRIPT . " restart");	
  }
  
  function monit_write_rcfile() {
  $rc = array();
  $rc['file'] = 'monit.sh';
  $rc['start'] = <<<EOD
  sleep 2
  pkill monit 2>/dev/null 
  /usr/local/bin/monit -c /usr/local/pkg/monit/monitrc

EOD;

  $rc['stop'] = <<<EOD
# Just to be sure...
sleep 2
pkill monit 2>/dev/null

EOD;

  conf_mount_rw();
  write_rcfile($rc);
  conf_mount_ro();
  }

  function monit_write_shortcutfile() {
    $shortcut_dir = '/usr/local/pkg/shortcuts/';  
    if (!file_exists($shortcut_dir)) {
      mkdir($shortcut_dir, 0777, true);
    }
   
    $monit_shortcuts = <<<'EOD'
<?php

global $shortcuts;

$shortcuts['monit'] = array();
$shortcuts['monit']['main'] = "monit/index.php";
$shortcuts['monit']['log'] = "/monit/monitor.php";
$shortcuts['monit']['service'] = "monit";

?>

EOD;

    $file = $shortcut_dir . 'pkg_monit.inc';

    file_put_contents($file, $monit_shortcuts); 

  }
?>
