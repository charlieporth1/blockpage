<?php
require('ctp-mods.php');
require('config.php');

$arg1_domain = $argv[1];
$arg2_time = $argv[2];

unblock($arg1_domain, $arg2_time, $conf);

?>
