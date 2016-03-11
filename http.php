<?php

require_once __DIR__ . '/vendor/autoload.php';

$climate = new League\CLImate\CLImate;

$options = [
    'http' => [
        'method'          => 'GET',
        'follow_location' => false,
        'ignore_errors'   => true,
        'timeout'         => 15,
    ],
];

$url = $argv[1];

function is_blocked($html)
{
    $titlePattern  = '/<title>10\.10\.34\.3[4-6]<\/title>/ism';
    $iframePattern = '/<iframe.*?src="http:\/\/10\.10\.34\.3[4-6]/ism';

    return preg_match($titlePattern, $html) || preg_match($iframePattern, $html);
}

function find_title($html)
{
    $titlePattern = '/<title>(.*?)<\/title>/ism';

    if (preg_match($titlePattern, $html, $matches)) {
        return trim($matches[1]);
    }

    return '<<UNKNOWN>>';
}

$climate->out('<blue>Result for: </blue>' . $url);
$progress = $climate->progress()->total(100);

$progress->advance(0, '<blue>Requesting ...</blue> ');
$context  = stream_context_create($options);
$response = @file_get_contents($url, false, $context);
$progress->advance(100, '<blue>Done!</blue> ');

if ($response !== false) {
    $httpStatus = $http_response_header[0];
    $status     = is_blocked($response) ? '<red>blocked</red>' : '<green>open</green>';
} else {
	$httpStatus = $status = '<<UNKNOWN>>';
}

$climate->table([
    [
        'Url'                  => $url,
        'HTTP Response Status' => $httpStatus,
        'Title'                => find_title($response),
        'Status'               => $status,
        'Date'                 => date('Y-m-d H:i:s'),
    ],
]);
