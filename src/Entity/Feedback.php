<?php

namespace App\Entity;

use App\Domain\Feedback\Dto\CreateFeedbackDto;
use App\Entity\Getters\FeedbackGetters;
use App\Entity\Setters\FeedbackSetters;
use App\Repository\FeedbackRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FeedbackRepository::class)]
class Feedback
{
    use FeedbackGetters;
    use FeedbackSetters;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(type: Types::STRING, length: 255)]
    private string $email;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT)]
    private string $message;

    #[ORM\Column(type: Types::STRING, length: 31, nullable: true)]
    private ?string $phoneNumber = null;

    public static function createFromDto(CreateFeedbackDto $dto): self
    {
        $feedback = new self();

        $feedback->setEmail($dto->email);
        $feedback->setName($dto->name);

        return  $feedback;
    }
}
