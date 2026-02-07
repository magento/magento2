<?php
/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\TestFramework\Catalog\Model;

use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Model\Category\Attribute\LayoutUpdateManager;

/**
 * Easy way to fake available files.
 */
class CategoryLayoutUpdateManager extends LayoutUpdateManager
{
    /**
     * @var array Keys are category IDs, values - file names.
     */
    private $fakeFiles = [];

    /**
     * Supply fake files for a category.
     *
     * @param int $forCategoryId
     * @param string[]|null $files Pass null to reset.
     */
    public function setCategoryFakeFiles(int $forCategoryId, ?array $files): void
    {
        if ($files === null) {
            unset($this->fakeFiles[$forCategoryId]);
        } else {
            $this->fakeFiles[$forCategoryId] = $files;
        }
    }

    /**
     * @inheritDoc
     */
    public function fetchAvailableFiles(CategoryInterface $category): array
    {
        $categoryId = $category->getId();
        if ($categoryId !== null && array_key_exists($categoryId, $this->fakeFiles)) {
            return $this->fakeFiles[$categoryId];
        }

        return parent::fetchAvailableFiles($category);
    }
}
