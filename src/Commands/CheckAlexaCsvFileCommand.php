<?php


namespace Filternet\Commands;


use Filternet\Checkers\Dns;
use Filternet\Checkers\Http;
use Filternet\Checkers\Sni;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CheckAlexaCsvFileCommand extends Command
{
    /**
     * @var array
     */
    protected $stats = [
        'sni' => 0,
        'http' => 0,
        'dns' => 0
    ];

    /**
     * @var array
     */
    protected $sites = [];
    /**
     * HTTP Timeout
     *
     * @var int
     */
    protected $httpTimeout;
    /**
     * @var string
     */
    private $sniHost;

    /**
     * Configure command
     */
    protected function configure()
    {
        $this
            ->setName('check:alexa')
            ->setDescription('Check alexa top 1m websites')
            ->addArgument(
                'path',
                InputArgument::REQUIRED,
                'Absolute Path to csv file (example /var/top-1m.csv)'
            )
            ->addOption(
                'max',
                'm',
                InputOption::VALUE_OPTIONAL,
                'Top x sites?',
                100
            )
            ->addOption(
                'output',
                'o',
                InputOption::VALUE_OPTIONAL,
                'Path to csv output file'
            )
            ->addOption(
                'timeout',
                't',
                InputOption::VALUE_OPTIONAL,
                'Http timeout',
                10
            )
            ->addOption(
                'host',
                null,
                InputOption::VALUE_OPTIONAL,
                'Connect to which host for SNI',
                'google.com'
            );
    }

    /**
     * Execute command
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $max = $input->getOption('max');
        $path = $input->getArgument('path');
        $outputFile = $input->getOption('output');
        $this->httpTimeout = $input->getOption('timeout');
        $this->sniHost = $input->getOption('host');

        if ($outputFile) {
            file_put_contents($outputFile, implode(',', [
                    'Rank',
                    'Site',
                    'SNI',
                    'HTTP',
                    'DNS'
                ]) . "\n", FILE_APPEND);
        }

        if (($handle = fopen($path, "r")) !== false) {
            while ((($data = fgetcsv($handle, 1000, ",")) !== false) && $max > 0) {
                list($rank, $domain) = $data;
                $this->check($rank, $domain, $output);

                if ($outputFile) {
                    file_put_contents($outputFile, implode(',', $this->sites[trim($rank)]) . "\n", FILE_APPEND);
                }

                $max--;
            }
            fclose($handle);
        }

        $output->writeln('Done!');

        $table = new Table($output);
        $table
            ->setHeaders(['SNI', 'HTTP', 'DNS'])
            ->setRows([$this->stats]);
        $table->render();
    }

    /**
     * Check site is blocked
     *
     * @param int $rank
     * @param string $domain
     * @param OutputInterface $output
     */
    protected function check($rank, $domain, OutputInterface $output)
    {
        $domain = trim($domain);
        $rank = trim($rank);

        $output->write("{$rank} - {$domain}: ");
        $this->sites[$rank] = [
            $rank,
            $domain,
            'unknown', // sni
            'unknown', // http
            'unknown', // dns
        ];

        $output->write($this->checkSni($rank, $domain));
        $output->write($this->checkHttp($rank, 'http://' . $domain));
        $output->write($this->checkDns($rank, $domain));

        $output->write("\n");

    }

    /**
     * Run SNI checker
     *
     * @param int $rank
     * @param string $domain
     * @return string
     */
    protected function checkSni($rank, $domain)
    {
        $sni = new Sni();
        $sni->setHost($this->sniHost);

        if ($sni->blocked($domain)) {
            $this->stats['sni']++;
            $this->sites[$rank][2] = 'blocked';
            return "<fg=red>[SNI] </>";
        }

        $this->sites[$rank][2] = 'open';
        return "<fg=green>[SNI] </>";
    }

    /**
     * Run HTTP checker
     *
     * @param int $rank
     * @param string $url
     * @return string
     */
    protected function checkHttp($rank, $url)
    {
        $http = new Http();
        $http->setTimeout($this->httpTimeout);

        if ($http->blocked($url)) {
            $this->stats['http']++;
            $this->sites[$rank][3] = 'blocked';
            return "<fg=red>[HTTP] </>";
        }

        $this->sites[$rank][3] = 'open';
        return "<fg=green>[HTTP] </>";
    }

    /**
     * Run DNS checker
     *
     * @param int $rank
     * @param string $domain
     * @return string
     */
    protected function checkDns($rank, $domain)
    {
        $loop = \React\EventLoop\Factory::create();
        $dns = new Dns($loop);

        $status = '';
        $dns->blocked($domain, function ($blocked) use (&$status, $rank) {
            if ($blocked) {
                $this->stats['dns']++;
                $this->sites[$rank][4] = 'blocked';
                $status = "<fg=red>[DNS] </>";
            } else {
                $this->sites[$rank][4] = 'open';
                $status = "<fg=green>[DNS] </>";
            }
        });

        $loop->run();

        return $status;

    }
}
