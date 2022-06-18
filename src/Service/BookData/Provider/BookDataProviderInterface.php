<?php

namespace App\Service\BookData\Provider;

use App\Domain\Book\Dto\NewBookDto;
use Traversable;

interface BookDataProviderInterface
{
    /**
     * @return Traversable<NewBookDto>
     */
    public function getData(): Traversable;
}
