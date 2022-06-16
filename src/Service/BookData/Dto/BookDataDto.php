<?php

namespace App\Service\BookData\Dto;

use DateTime;
use DateTimeInterface;
use Exception;

class BookDataDto
{
    public ?string $title = null;

    public ?string $isbn = null;

    public int $pageCount = 0;

    public ?DateTimeInterface $publishedDate = null;

    public ?string $thumbnailUrl = null;

    public ?string $shortDescription = null;

    public ?string $longDescription = null;

    public ?string $status = null;

    /**
     * @var string[]
     */
    public array $authors = [];

    /**
     * @var string[]
     */
    public array $categories = [];

    /**
     * @throws Exception
     */
    public static function hydrateFrom(array $data): self
    {
        $dto = new self();

        foreach ($data as $property => $value) {
            if (property_exists($dto, $property)) {
                if ('publishedDate' === $property) {
                    $value = new DateTime($value->{'$date'});
                }

                $dto->$property = $value;
            }
        }

        return $dto;
    }
}
