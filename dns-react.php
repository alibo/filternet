<?php

require_once __DIR__ . '/vendor/autoload.php';

$loop    = React\EventLoop\Factory::create();
$factory = new React\Dns\Resolver\Factory();
$dns     = $factory->create('8.8.8.8', $loop);

$name = $argv[1];

$climate = new League\CLImate\CLImate;
$climate->out('Result for: ' . $name);
$progress = $climate->progress()->total(100);

$progress->advance(0, '<blue>Resolving ...</blue>');

$promise = React\Promise\all([
    $dns->resolve($name),
    $dns->resolve(strtoupper($name)),
]);

$promise->then(function ($response) use ($progress, $name) {
    $progress->advance(100, '<blue>Done!</blue>');

	$lowerStatus = is_blocked($response[0]) ? '<red>blocked</red>' : '<green>open</green>';
	$upperStatus = is_blocked($response[1]) ? '<red>blocked</red>' : '<green>open</green>';

    return [
        [
            'Host'   => $name,
            'IP'     => $response[0],
            'Status' => $lowerStatus,
            'Date'   => date('Y-m-d H:i:s'),
        ],
        [
            'Host'   => strtoupper($name),
            'IP'     => $response[1],
            'Status' => $upperStatus,
            'Date'   => date('Y-m-d H:i:s'),
        ],
    ];

})->then(function ($data) use ($climate) {
	$climate->table($data);
});

function is_blocked($ip)
{
    return preg_match('/^10\.10\.34\.3[4-6]$/', $ip);
}

$loop->run();
