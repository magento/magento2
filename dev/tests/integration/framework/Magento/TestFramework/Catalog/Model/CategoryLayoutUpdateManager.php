<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
        if (array_key_exists($category->getId(), $this->fakeFiles)) {
            return $this->fakeFiles[$category->getId()];
        }

        return parent::fetchAvailableFiles($category);
    }
}
