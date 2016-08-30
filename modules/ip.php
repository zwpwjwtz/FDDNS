<?php
function getLocalIP()
{
    $output=shell_exec('ip addr show to 0/0 scope global');
    $p=strpos($output, 'inet');
    $p2=strpos($output, '/', $p);
    $ip=substr($output, $p+5, $p2-$p-5);
    if (empty($ip))
        $ip=getHostByName(getHostName());
    return $ip;
}
function getLocalIPv6()
{
    $output=shell_exec('ip addr show to ::/0 scope global');
    $p=strpos($output, 'inet6');
    $p2=strpos($output, '/', $p);
    return substr($output, $p+6, $p2-$p-6);
}
?>
