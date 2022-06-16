<?php

namespace App\Service\BookData\Provider;

use App\Service\BookData\Dto\BookDataDto;
use Traversable;

interface BookDataProviderInterface
{
    /**
     * @return Traversable<BookDataDto>
     */
    public function getData(): Traversable;
}
