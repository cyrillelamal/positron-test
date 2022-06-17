<?php

namespace App\Command;

use App\Domain\Book\UseCase\InsertNewBooks;
use App\Service\BookData\Provider\BookDataProviderInterface;
use Exception;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:parse-books',
    description: 'Parse books from a third-party service.',
)]
class ParseBooksCommand extends Command implements LoggerAwareInterface
{
    private LoggerInterface $logger;

    private BookDataProviderInterface $bookDataProvider;
    private InsertNewBooks $insertNewBooks;

    public function __construct(
        BookDataProviderInterface $bookDataProvider,
        InsertNewBooks            $insertNewBooks,
    )
    {
        $this->bookDataProvider = $bookDataProvider;
        $this->insertNewBooks = $insertNewBooks;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('v', null, InputOption::VALUE_NONE, 'Verbosity level');
        // TODO: implement verbosity option, e.g. using listener or callback on insertNewBooks action
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $this->logger->debug('Downloading books.', ['provider' => $this->bookDataProvider]);
            $io->info('Downloading books.');
            $data = $this->bookDataProvider->getData();

            $this->logger->debug('Appending books.');
            $io->info('Appending books.');
            ($this->insertNewBooks)($data);

            $io->success('New books have been inserted!');
        } catch (Exception) {
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }
}
