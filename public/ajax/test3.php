<?php

$ip = $_GET['ip'];
$myip = GetHostByName($REMOTE_ADDR);

// ********* Ping **************
$output = shell_exec("ping -c 4 ".$myip);
echo "<b>Pinging ".$myip."</b>:<br /><pre>$output</pre>";
$output = shell_exec("ping -c 4 ".$ip);
echo "<b>Pinging ".$ip."</b>:<br /><pre>$output</pre>";
