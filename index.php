<?php
define('FDDNS_ROOT','FDDNS_ROOT');
require('config.php');

if (RUN_MODE == 'client')
    require('client.php');
else
    require('server.php');
?>
