<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider;

use Magento\CatalogGraphQl\Model\Category\Hydrator;
use Magento\Catalog\Api\Data\CategoryInterface;

/**
 * Extract data from category tree
 */
class ExtractDataFromCategoryTree
{
    /**
     * @var Hydrator
     */
    private $categoryHydrator;

    /**
     * @param Hydrator $categoryHydrator
     */
    public function __construct(
        Hydrator $categoryHydrator
    ) {
        $this->categoryHydrator = $categoryHydrator;
    }

    /**
     * Extract data from category tree
     *
     * @param \Iterator $iterator
     * @return array
     */
    public function execute(\Iterator $iterator): array
    {
        $tree = [];
        while ($iterator->valid()) {
            /** @var CategoryInterface $category */
            $category = $iterator->current();
            $iterator->next();
            $nextCategory = $iterator->current();
            $tree[$category->getId()] = $this->categoryHydrator->hydrateCategory($category);
            $tree[$category->getId()]['model'] = $category;
            if ($nextCategory && (int) $nextCategory->getLevel() !== (int) $category->getLevel()) {
                $tree[$category->getId()]['children'] = $this->execute($iterator);
            }
        }

        return $tree;
    }
}
