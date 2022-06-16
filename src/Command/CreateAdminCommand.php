<?php

namespace App\Command;

use App\Domain\User\Dto\CreateUserDto;
use App\Domain\User\Event\UserCreatedEvent;
use App\Domain\User\Exception\BadUserDataException;
use App\Domain\User\Exception\UserAlreadyExistsException;
use App\Domain\User\Role;
use App\Domain\User\UseCase\CreateUser;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

#[AsCommand(
    name: 'app:create-admin',
    description: 'Create a new user with admin privileges.',
)]
class CreateAdminCommand extends Command
{
    private CreateUser $createUser;
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(
        CreateUser               $createUser,
        EventDispatcherInterface $eventDispatcher,
    )
    {
        $this->createUser = $createUser;
        $this->eventDispatcher = $eventDispatcher;

        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $data = new CreateUserDto();
        $data->email = $io->ask("What is administrator's email?");
        $data->plainPassword = $io->askHidden("What is administrator's password?"); // TODO: repeat the question.
        $data->roles = [Role::ADMIN];

        try {
            $user = ($this->createUser)($data);
            $this->eventDispatcher->dispatch(new UserCreatedEvent($user), UserCreatedEvent::NAME);
            $io->info("User created: {$user->getEmail()}");
            return Command::SUCCESS;
        } catch (BadUserDataException $e) {
            $io->error((string)$e->getErrors()); // TODO: format errors
        } catch (UserAlreadyExistsException $e) {
            $io->error("User \"{$e->getUserIdentifier()}\" already exists.");  // TODO: i18n
        }

        return Command::FAILURE;
    }
}
