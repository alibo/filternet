<?php

namespace Filternet\Commands;

use Filternet\Checkers\Http;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CheckHttpCommand extends Command
{
    /**
     * Configure command
     */
    protected function configure()
    {
        $this
            ->setName('check:http')
            ->setDescription('Is blocked by HTTP header')
            ->addArgument(
                'url',
                InputArgument::REQUIRED,
                'Url (example: http://dropbox.com)'
            )
            ->addOption(
                'timeout',
                't',
                InputOption::VALUE_OPTIONAL,
                'Http timeout',
                15
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
        $url = $input->getArgument('url');
        $timeout = $input->getOption('timeout');

        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            $output->writeln('<error>Url is invalid!</error>');
            return;
        }

        $http = new Http();
        $http->setTimeout($timeout);
        $progress = new ProgressBar($output, 100);
        $progress->advance(10);
        $result = $http->check($url);
        $progress->finish();

        $output->writeln(' Done!');

        $table = new Table($output);
        $table
            ->setHeaders(['Url', 'HTTP Response Status', 'Title', 'Status', 'Date'])
            ->setRows([$result]);
        $table->render();


    }
}