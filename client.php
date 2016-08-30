<?php
if (!defined('FDDNS_ROOT')) exit(0);
require('modules/enc.php');
require('modules/curl.php');
require('modules/ip.php');

// Get IP for pushing
$localIP='';
if (LOCAL_IP_BY_API)
{
    $localIP=curl_getData("http://v4.ipv6-test.com/api/myip.php");
}
if (!$localIP)
    $localIP=getLocalIP();
    
$localIPv6='';
if (LOCAL_IPv6_BY_API)
{
    $localIPv6=curl_getData("http://v6.ipv6-test.com/api/myip.php");
}
if (!$localIPv6)
    $localIPv6=getLocalIPv6();

// Build FDDNS request body
$vendors=array();
foreach($FDDNS_verdorList as $vendorName)
{
    $records=array($vendorName);
    $records[]=implode(SEPERATOR_FIELD, array($localIP,'A'));
    $records[]=implode(SEPERATOR_FIELD, array($localIPv6,'AAAA'));
    $vendors[]=implode(SEPERATOR_RECORD, $records);
}
$content='';
for($i=0; $i<9; $i++)
{
    // Add random bytes for security of CSC encryption
    $content.=chr(rand(0,255));
}
$content=$content.implode(SEPERATOR_VENDOR, $vendors);

// Build FDDNS request head
$header=str_repeat("\0", HEADER_LEN);
$header=substr_replace($header, intTo4Bytes(crc32($content)), CRC_POS, CRC_LEN);

// Encrypt the body, then send request to server
$encoder=new CSC1();
echo curl_postData(SERVER_URL, $header.$encoder->encrypt($content, COMMUNICATION_KEY));

exit(0);
?>