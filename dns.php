<?php

require_once __DIR__ . '/vendor/autoload.php';

$climate = new League\CLImate\CLImate;

$lowerName = $argv[1];

function is_blocked($ip)
{
    return preg_match('/^10\.10\.34\.3[4-6]$/', $ip);
}

function resolve($host)
{
    return dns_get_record($host, DNS_A);
}

$climate->out('Result for: ' . $lowerName);
$progress = $climate->progress()->total(100);

$progress->advance(0, '<blue>Resolving</blue> ' . $lowerName);
$lowerResult = resolve($lowerName);
$progress->advance(100, '<blue>Done</blue>');

$lowerStatus = is_blocked($lowerResult[0]['ip']) ? '<red>blocked</red>' : '<green>open</green>';

$climate->table([
    [
        'Host Name' => $lowerResult[0]['host'],
        'IP'        => $lowerResult[0]['ip'],
        'TTL'       => $lowerResult[0]['ttl'],
        'Status'    => $lowerStatus,
        'Date'      => date('Y-m-d H:i:s'),
    ]
]);
