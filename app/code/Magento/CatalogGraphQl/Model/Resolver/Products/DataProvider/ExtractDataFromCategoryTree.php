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
        $referenceList = []; // A list of pointer to parents, used to know where to insert new children

        // First item is laways root, create root node
        /** @var CategoryInterface $category */
        $category = $iterator->current();
        $tree[$category->getId()] = $this->categoryHydrator->hydrateCategory($category);
        $tree[$category->getId()]['model'] = $category;
        $referenceList[$category->getId()] = &$tree[$category->getId()];

        $iterator->next();

        // Fill the rest of tree
        while ($iterator->valid()) {
            /** @var CategoryInterface $category */
            $category = $iterator->current();

            $categoryData = $this->categoryHydrator->hydrateCategory($category);
            $categoryData['model'] = $category;

            $newItemIdAtItsParent = count($referenceList[$category->getParentId()]['children']);
            $referenceList[$category->getParentId()]['children'][$newItemIdAtItsParent] = $categoryData;
            $referenceList[$category->getId()] = &$referenceList[$category->getParentId()]['children'][$newItemIdAtItsParent];

            $iterator->next();
        }

        return $tree;
    }
}
