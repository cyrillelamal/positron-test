<?php

namespace App\Service\BookData\Provider;

use App\Domain\Book\Dto\CreateBookDto;
use Traversable;

interface BookDataProviderInterface
{
    /**
     * @return Traversable<CreateBookDto>
     */
    public function getData(): Traversable;
}
