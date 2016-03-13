<?php

namespace Filternet\Commands;

use Filternet\Checkers\Dns;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CheckDnsCommand extends Command
{
    /**
     * Configure command
     */
    protected function configure()
    {
        $this
            ->setName('check:dns')
            ->setDescription('Is blocked by DNS?')
            ->addArgument(
                'domain',
                InputArgument::REQUIRED,
                'Domain name (example: youtube.com)'
            )
            ->addOption(
                'server',
                's',
                InputOption::VALUE_OPTIONAL,
                'Server name (example: 8.8.8.8)',
                '8.8.8.8'
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
        $domain = $input->getArgument('domain');
        $server = $input->getOption('server');

        $loop = \React\EventLoop\Factory::create();
        $dns = new Dns($loop);

        $progress = new ProgressBar($output, 2);

        $dns->check($domain, $server)->then(
            function ($result) use ($progress, $output) {
                $progress->finish();

                $output->writeln(' Done!');

                $table = new Table($output);
                $table
                    ->setHeaders(['Domain', 'IP', 'Status', 'Date'])
                    ->setRows($result);

                $table->render();
            },
            function ($e) use ($output) {
                $output->writeln("<error>Error: {$e->getMessage()}</error>");
            }
        );

        $loop->run();
    }
}