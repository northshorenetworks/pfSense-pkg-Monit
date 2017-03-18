<?php

  define('MONIT_CONFIG_DIR', '/usr/local/pkg/monit/');
  define('MONIT_STARTUP_SCRIPT', '/usr/local/etc/rc.d/monit.sh');
  
  require_once('config.inc');
  require_once('service-utils.inc');

  global $config, $g;

  if (!is_array($config['installedpackages']['monitglobal']))
    $config['installedpackages']['monitglobal'] = array();

  $monit = &$config['installedpackages']['monitglobal'];

  function is_monit_running() {
  	exec("pgrep monit", $pids);
	if(empty($pids)) {
		return false;
	} else {
		return true;
        }
  }

  function monit_validate_config () {
  	exec('/usr/local/bin/monit -c /usr/local/pkg/monit/monitrc', $output, $return);
	if (!$return) {
	    return true;
	} else {
	    return false;
	}
  }

  function monit_write_config() {
    global $config;
    $monit = &$config['installedpackages']['monitglobal'];
	
    $fd = fopen(MONIT_CONFIG_DIR . 'monitrc', 'w');
    fwrite($fd, base64_decode($monit['monit_config']));
    fclose($fd);
    chmod(MONIT_CONFIG_DIR . 'monitrc', 0700);
   
    # test the config to see if it parses, log an error if it doesn't reload it
    if (monit_validate_config()) {
	if (is_monit_running()) {
		// Reload the new configuration
    		exec(MONIT_STARTUP_SCRIPT . " reload");	
	} else {
		// Start the monit service
    		exec(MONIT_STARTUP_SCRIPT . " start");	
	}
    } else {
	log_error("There were errors in the monit config file, please review");
    }
    exec(MONIT_STARTUP_SCRIPT . " restart");	
  }
  
  function monit_write_rcfile() {
  $rc = array();
  $rc['file'] = 'monit.sh';
  $rc['start'] = <<<EOD
  sleep 2
  pkill monit 2>/dev/null 
  /usr/local/bin/monit -c /usr/local/pkg/monit/monitrc -l /var/log/monit.log -p /var/run/monit.pid

EOD;

  $rc['stop'] = <<<EOD
# Just to be sure...
sleep 2
pkill monit 2>/dev/null

EOD;

  $rc['reload'] = <<<EOD
  /usr/local/bin/monit -c /usr/local/pkg/monit/monitrc -l /var/log/monit.log -p /var/run/monit.pid reload
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
$shortcuts['monit']['main'] = "packages/monit/index.php";
$shortcuts['monit']['service'] = "monit";

?>

EOD;

    $file = $shortcut_dir . 'pkg_monit.inc';

    file_put_contents($file, $monit_shortcuts); 

  }
?>
