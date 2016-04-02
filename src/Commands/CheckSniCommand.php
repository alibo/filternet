<?php

namespace Filternet\Commands;

use Filternet\Checkers\Sni;
use Filternet\Utility\Status;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CheckSniCommand extends Command
{
    /**
     * Configure command
     */
    protected function configure()
    {
        $this
            ->setName('check:sni')
            ->setDescription('Is blocked by TLS SNI?')
            ->addArgument(
                'domain',
                InputArgument::REQUIRED,
                'Domain name (example: youtube.com)'
            )
            ->addOption(
                'host',
                null,
                InputOption::VALUE_OPTIONAL,
                'Connect to which host (example: google.com)',
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
        $lowerDomain = $input->getArgument('domain');
        $upperDomain = strtoupper($lowerDomain);

        $sni = new Sni();
        $sni->setHost($input->getOption('host'));

        $progress = new ProgressBar($output, 2);

        $lowerStatus = $this->status($sni->blocked($lowerDomain));
        $progress->advance();
        $upperStatus = $this->status($sni->blocked($upperDomain));
        $progress->finish();

        $date = date('Y-m-d H:i:s');

        $output->writeln(' Done!');

        $table = new Table($output);
        $table
            ->setHeaders(['SNI Name', 'Status', 'Date'])
            ->setRows([
                [$lowerDomain, $lowerStatus, $date],
                [$upperDomain, $upperStatus, $date]
            ]);
        $table->render();
    }

    /**
     * Get Status
     *
     * @param bool $blocked
     * @return string
     */
    protected function status($blocked)
    {
        return $blocked ? Status::blocked() : Status::open();
    }

}