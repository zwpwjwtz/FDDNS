<?php
if (!defined('FDDNS_ROOT')) exit(0);
require __DIR__.'/dnspod.php';
require __DIR__.'/config.php'; // pseudo config
if (file_exists(__DIR__.'/../config.php')) {
    require __DIR__.'/../config.php';
}

function getRecord($vendor, $dnsRecord)
{
    return $vendor->apiCall('Record.List',
        array('domain_id' => $dnsRecord->domainID,
        'sub_domain' => $dnsRecord->subDomain
        )
    );
}

function updateRecord($vendor, $dnsRecord)
{
    return $vendor->apiCall('Record.Modify',
        array('domain_id' => $dnsRecord->domainID,
            'record_id' => $dnsRecord->recordID,
            'sub_domain' => $dnsRecord->subDomain,
            'record_type' => $dnsRecord->recordType,
            'record_line' => $dnsRecord->recordLine,
            'value' => $dnsRecord->ipAddress
        )
    );
}

$dnsRecord = new DnsRecord();
$dnsRecord->domainID = $CONFIG_API->domainID;
$dnsRecord->subDomain = $CONFIG_API->subDomain;

$dnspod = new dnspod($CONFIG_API->globalServer,
                     $CONFIG_API->apiID,
                     $CONFIG_API->apiToken);

// Get DNS record
$response = getRecord($dnspod, $dnsRecord);
$records = $response["records"];
if (count($records) < 1) {
    exit("Error: no dns record return\n");
}

// Get my IP address
$myIP = '';
$myIPv6 = '';
foreach($FDDNS_IPs as $ip)
{
    $fields = explode(SEPERATOR_FIELD, $ip);
    switch($fields[1]) {
        case 'A':
            $myIP = $fields[0];
            break;
        case 'AAAA':
            $myIPv6 = $fields[0];
            break;
        default:
    }
}

if (!$myIP && !$myIPv6)
{
    exit("No IP info can be obtained. Exit.\n");
}

foreach($records as $record)
{
    $dnsRecord->ipAddress = $record["value"];
    $dnsRecord->recordID = $record["id"];
    $dnsRecord->recordLine = strtolower($record["line"]);
    $dnsRecord->recordType = $record["type"];

    // Update DNS record
    $needUpdate = false;
    switch($dnsRecord->recordType) {
        case  'A':
            if (!$myIP) break;
            if ($myIP == $dnsRecord->ipAddress) {
                echo("Notice: IP does not change\n");
            }
            else {
                $needUpdate = true;
                $dnsRecord->ipAddress = $myIP;
                $myIP = '';
            }
            break;
        case 'AAAA':
            if (!$myIPv6) break;
            if ($myIPv6 == $dnsRecord->ipAddress) {
                echo("Notice: IP(v6) does not change\n");
            }
            else {
                $needUpdate = true;
                $dnsRecord->ipAddress = $myIPv6;
                $myIPv6 = '';
            }
            break;
        default:
    }
    if (!$needUpdate) continue;
    updateRecord($dnspod, $dnsRecord);

    echo("Notice: IP change to ".$dnsRecord->ipAddress."\n");
}
