<?php

namespace App\Domain\Feedback\Dto;

use Symfony\Component\Validator\Constraints as Assert;


class CreateFeedbackDto
{
    #[Assert\NotNull]
    #[Assert\NotBlank]
    #[Assert\Email]
    public string $email = '';

    #[Assert\AtLeastOneOf(constraints: [
        new Assert\IsNull(),
        new Assert\Sequentially(constraints: [
            new Assert\NotBlank(),
            new Assert\Length(min: 1, max: 255)
        ]),
    ])]
    public ?string $name = null;

    #[Assert\NotNull]
    #[Assert\NotBlank]
    public string $message = '';

    #[Assert\AtLeastOneOf(constraints: [
        new Assert\IsNull(),
        new Assert\Sequentially(constraints: [
            new Assert\NotBlank(),
            new Assert\Length(min: 1, max: 31)
        ]),
    ])]
    public ?string $phoneNumber = null;

    public function __construct(
        string  $email = '',
        ?string $name = null,
        string  $message = '',
        ?string $phoneNumber = null,
    )
    {
        $this->email = $email;
        $this->name = $name;
        $this->message = $message;
        $this->phoneNumber = $phoneNumber;
    }
}
