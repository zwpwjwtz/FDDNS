<?php
if (!defined('FDDNS_ROOT')) exit(0);
require('modules/enc.php');
require('modules/curl.php');

// Get request for client and verify it
$buffer=file_get_contents("php://input");
$header=substr($buffer, 0, HEADER_LEN);
$encoder=new CSC1();
$content=$encoder->decrypt(substr($buffer, HEADER_LEN), COMMUNICATION_KEY);
if (intTo4Bytes(crc32($content)) != substr($header, CRC_POS, CRC_LEN))
    exit(0);

// Parse request body
$vendors=explode(SEPERATOR_VENDOR, substr($content, 9));
global $fddns_ip;
foreach($vendors as $vendor)
{
    $records=explode(SEPERATOR_RECORD, $vendor);
    $vendor_name=$records[0];
    if (empty($vendor))
        continue;
    
    // Prepare records for each vendor
    $FDDNS_IPs = array_slice($records, 1);
    
    // Invoke the vendor
    include('vendor/'.$vendor_name.'/ddns.php');
}

exit(0);
?>