<?php

namespace App\Command;

use App\Domain\Book\Dto\NewBookDto;
use App\Service\BookData\Provider\BookDataProviderInterface;
use Exception;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * @todo i18n
 */
#[AsCommand(
    name: 'app:parse-books',
    description: 'Parse books from a third-party service.',
)]
class ParseBooksCommand extends Command implements LoggerAwareInterface
{
    private LoggerInterface $logger;

    private BookDataProviderInterface $bookDataProvider;
    private MessageBusInterface $bus;

    public function __construct(
        BookDataProviderInterface $bookDataProvider,
        MessageBusInterface       $bus,
    )
    {
        $this->bookDataProvider = $bookDataProvider;
        $this->bus = $bus;

        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $this->logger->debug('Fetching book data.', ['provider' => $this->bookDataProvider]);
            $io->info('Fetching book data.');

            foreach ($this->bookDataProvider->getData() as $data) /** @var NewBookDto $data */ {
                $this->logger->info('Adding book.', ['data' => $data]);
                if ($input->getOption('verbose')) {
                    $io->info("Processing book \"{$data->title}\".");
                }

                $this->bus->dispatch($data);
            }

            $io->success('New books have been added!');
        } catch (Exception $e) {
            $this->logger->error('Unexpected exception while parsing.', ['exception' => $e]);
            $io->error('Something went wrong.');
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }
}
