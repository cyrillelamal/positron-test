<?php

namespace App\Domain\Category\DefaultCategory;

use App\Domain\Category\Category;

interface DefaultCategoryProviderInterface
{
    /**
     * @return Category[]
     */
    public function getDefaultCategories(): array;
}
