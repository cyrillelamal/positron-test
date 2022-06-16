<?php

namespace App\Domain\User\UseCase;

use App\Domain\User\Dto\CreateUserDto;
use App\Domain\User\Exception\BadUserDataException;
use App\Domain\User\Exception\UserAlreadyExistsException;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CreateUser
{
    private EntityManagerInterface $entityManager;
    private UserPasswordHasherInterface $passwordHasher;
    private ValidatorInterface $validator;

    public function __construct(
        EntityManagerInterface      $entityManager,
        UserPasswordHasherInterface $passwordHasher,
        ValidatorInterface          $validator,
    )
    {
        $this->entityManager = $entityManager;
        $this->passwordHasher = $passwordHasher;
        $this->validator = $validator;
    }

    /**
     * @throws BadUserDataException
     * @throws UserAlreadyExistsException
     */
    public function __invoke(CreateUserDto $data): User
    {
        $this->validateInput($data);

        $user = $this->makeUser($data);

        $this->entityManager->beginTransaction();
        try {
            $this->validateIfUserIsUnique($user);

            $this->entityManager->persist($user);
            $this->entityManager->flush();
            $this->entityManager->commit();
        } catch (Exception $exception) {
            $this->entityManager->rollback();
            throw $exception;
        }

        return $user;
    }

    /**
     * Simple factory.
     * Make a new user model WITHOUT actually saving it into the database.
     */
    protected function makeUser(CreateUserDto $data): User
    {
        $user = new User();

        $user->setEmail($data->email);
        $user->setPassword($this->passwordHasher->hashPassword($user, $data->plainPassword));
        $user->setRoles($data->roles);

        return $user;
    }

    /**
     * @throws BadUserDataException
     */
    protected function validateInput(CreateUserDto $data): void
    {
        $errors = $this->validator->validate($data);

        if ($errors->count() > 0) {
            throw new BadUserDataException($errors);
        }
    }

    /**
     * @throws UserAlreadyExistsException
     */
    protected function validateIfUserIsUnique(User $user): void
    {
        $errors = $this->validator->validate($user);

        if ($errors->count() > 0) {
            throw new UserAlreadyExistsException($user->getEmail());
        }
    }
}
