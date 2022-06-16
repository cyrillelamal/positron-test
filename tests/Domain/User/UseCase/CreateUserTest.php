<?php

namespace App\Tests\Domain\User\UseCase;

use App\Domain\User\Dto\CreateUserDto;
use App\Domain\User\Exception\BadUserDataException;
use App\Domain\User\Exception\UserAlreadyExistsException;
use App\Domain\User\Role;
use App\Domain\User\UseCase\CreateUser;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CreateUserTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * @throws UserAlreadyExistsException
     * @throws BadUserDataException
     */
    public function testItValidatesTheProvidedInput(): void
    {
        $data = $this->getValidInputData();

        $validator = $this->createMock(ValidatorInterface::class);
        $validator->expects($this->atLeast(1))
            ->method('validate')
            ->withConsecutive([$data]);

        (new CreateUser(
            $this->createMock(EntityManagerInterface::class),
            $this->createMock(UserPasswordHasherInterface::class),
            $validator,
        ))($data);
    }

    /**
     * @throws UserAlreadyExistsException
     * @throws BadUserDataException
     */
    public function testItStoresTheCreatedUserInTheDatabase(): void
    {
        $data = $this->getValidInputData();

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects($this->once())->method('persist');
        $entityManager->expects($this->once())->method('flush');

        (new CreateUser(
            $entityManager,
            $this->createMock(UserPasswordHasherInterface::class),
            $this->createMock(ValidatorInterface::class)
        ))($data);
    }

    /**
     * @throws UserAlreadyExistsException
     * @throws BadUserDataException
     */
    public function testItHashesPassword(): void
    {
        $data = $this->getValidInputData();

        $hasher = $this->createMock(UserPasswordHasherInterface::class);
        $hasher->method('hashPassword')->willReturn('hashed');
        $hasher->expects($this->once())->method('hashPassword');

        $user = (new CreateUser(
            $this->createMock(EntityManagerInterface::class),
            $hasher,
            $this->createMock(ValidatorInterface::class)
        ))($data);

        $this->assertNotEquals($data->plainPassword, $user->getPassword());
    }

    private function getValidInputData(): CreateUserDto
    {
        $data = new CreateUserDto();

        $data->email = 'foo@bar.com';
        $data->plainPassword = 'plain-password';
        $data->roles = [Role::USER];

        return $data;
    }
}
