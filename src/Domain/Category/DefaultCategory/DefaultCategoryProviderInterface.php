<?php

namespace App\Domain\Category\DefaultCategory;

use App\Entity\Category;

interface DefaultCategoryProviderInterface
{
    /**
     * @return Category[]
     */
    public function getDefaultCategories(): array;
}
