<?php

namespace Filternet\Commands;

use Filternet\Checkers\Sni;
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
        $progress = new ProgressBar($output, 2);

        $lowerResult = $sni->blocked($lowerDomain) ? $this->blockedText() : $this->openText();
        $progress->advance();
        $upperResult = $sni->blocked($upperDomain) ? $this->blockedText() : $this->openText();
        $progress->finish();

        $date = date('Y-m-d H:i:s');

        $output->writeln(' Done!');

        $table = new Table($output);
        $table
            ->setHeaders(['SNI Name', 'Status', 'Date'])
            ->setRows([
                [$lowerDomain, $lowerResult, $date],
                [$upperDomain, $upperResult, $date]
            ]);
        $table->render();


    }


    /**
     * Get blocked status text
     *
     * @return string
     */
    protected function blockedText()
    {
        return '<fg=red>Blocked</>';
    }

    /**
     * Get open status text
     *
     * @return string
     */
    protected function openText()
    {
        return '<fg=green>Open</>';
    }
}