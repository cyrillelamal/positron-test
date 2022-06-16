<?php

namespace App\Domain\User\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class CreateUserDto
{
    #[Assert\NotBlank]
    #[Assert\Email]
    public string $email = '';

    #[Assert\NotNull]
    #[Assert\NotBlank]
    #[Assert\Length(min: 3, max: 255)]
    public string $plainPassword = '';

    public array $roles = [];
}
