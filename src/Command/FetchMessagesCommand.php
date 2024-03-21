<?php

namespace App\Command;

use App\Fetcher\MessageFetcherInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:messages:fetch',
    description: 'Fetch messages from mail server',
)]
class FetchMessagesCommand extends Command
{
    public function __construct(
        private MessageFetcherInterface $messageFetcher,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        
        $this->messageFetcher->fetch();

        $io->success('Messages have been fetched successfully');

        return Command::SUCCESS;
    }
}
