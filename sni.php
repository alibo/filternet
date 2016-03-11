<?php

require_once __DIR__ . '/vendor/autoload.php';

$climate = new League\CLImate\CLImate;

function check_sni($name)
{
    $options = [
        'http' => [
            'method'          => 'GET',
            'follow_location' => false,
        ],
        'ssl'  => [
            'verify_peer'             => true,
            'verify_peer_name'        => false,
            'SNI_enabled'             => true,
            'disable_compression'     => false,
            'peer_name'               => $name,
            'capture_peer_cert'       => true,
            'capture_peer_cert_chain' => true,
            'capture_session_meta'    => true,
        ],
    ];

    $context  = stream_context_create($options);
    @file_get_contents('https://google.com', false, $context);

    $meta = stream_context_get_options($context);
    
    return isset($meta['ssl']['session_meta']['protocol']);
}

$name  = trim($argv[1]);
$upper = strtoupper($name);

$climate->out('Result for: ' . $name);
$progress = $climate->progress()->total(100);

$progress->advance(0, '<blue>Checking</blue> ' . $name);
$lowerStatus = check_sni($name) ? '<green>open</green>' : '<red>blocked</red>';

$progress->advance(50, '<blue>Checking</blue> ' . $upper);
$upperStatus = check_sni($upper) ? '<green>open</green>' : '<red>blocked</red>';

$progress->advance(50, 'Done!');
$climate->table([
    [
        'sni-name' => $name,
        'status' => $lowerStatus,
        'date' => date('Y-m-d H:i:s')
    ],
    [
        'sni-name' => $upper,
        'status' => $upperStatus,
        'date' => date('Y-m-d H:i:s')
    ]
]);
