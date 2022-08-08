<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider;

use Magento\CatalogGraphQl\Model\Category\Hydrator;
use Magento\Catalog\Api\Data\CategoryInterface;

class ExtractDataFromCategoryTree
{
    /**
     * @var Hydrator
     */
    private $categoryHydrator;

    /**
     * @var CategoryInterface
     */
    private $iteratingCategory;

    /**
     * @var int
     */
    private $startCategoryFetchLevel = 1;

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
        /** @var CategoryInterface $rootCategory */
        $rootCategory = $iterator->current();
        while ($iterator->valid()) {
            /** @var CategoryInterface $currentCategory */
            $currentCategory = $iterator->current();
            $iterator->next();
            if ($this->areParentsActive($currentCategory, $rootCategory, (array)$iterator)) {
                $pathElements = $currentCategory->getPath() !== null ?
                    explode("/", $currentCategory->getPath()) : [''];
                if (empty($tree)) {
                    $this->startCategoryFetchLevel = count($pathElements) - 1;
                }
                $this->iteratingCategory = $currentCategory;
                $currentLevelTree = $this->explodePathToArray($pathElements, $this->startCategoryFetchLevel);
                if (empty($tree)) {
                    $tree = $currentLevelTree;
                }
                $tree = $this->mergeCategoriesTrees($tree, $currentLevelTree);
            }
        }

        return $this->sortTree($tree);
    }

    /**
     * Test that all parents of the current category are active.
     *
     * Assumes that $categoriesArray are key-pair values and key is the ID of the category and
     * all categories in this list are queried as active.
     *
     * @param CategoryInterface $currentCategory
     * @param CategoryInterface $rootCategory
     * @param array $categoriesArray
     * @return bool
     */
    private function areParentsActive(
        CategoryInterface $currentCategory,
        CategoryInterface $rootCategory,
        array $categoriesArray
    ): bool {
        if ($currentCategory === $rootCategory) {
            return true;
        } elseif (array_key_exists($currentCategory->getParentId(), $categoriesArray)) {
            return $this->areParentsActive(
                $categoriesArray[$currentCategory->getParentId()],
                $rootCategory,
                $categoriesArray
            );
        } else {
            return false;
        }
    }

    /**
     * Merge together complex categories trees
     *
     * @param array $tree1
     * @param array $tree2
     * @return array
     */
    private function mergeCategoriesTrees(array &$tree1, array &$tree2): array
    {
        $mergedTree = $tree1;
        foreach ($tree2 as $currentKey => &$value) {
            if (is_array($value) && isset($mergedTree[$currentKey]) && is_array($mergedTree[$currentKey])) {
                $mergedTree[$currentKey] = $this->mergeCategoriesTrees($mergedTree[$currentKey], $value);
            } else {
                $mergedTree[$currentKey] = $value;
            }
        }
        return $mergedTree;
    }

    /**
     * Recursive method to generate tree for one category path
     *
     * @param array $pathElements
     * @param int $index
     * @return array
     */
    private function explodePathToArray(array $pathElements, int $index): array
    {
        $tree = [];
        $tree[$pathElements[$index]]['id'] = $pathElements[$index];
        if ($index === count($pathElements) - 1) {
            $tree[$pathElements[$index]] = $this->categoryHydrator->hydrateCategory($this->iteratingCategory);
            $tree[$pathElements[$index]]['model'] = $this->iteratingCategory;
        }
        $currentIndex = $index;
        $index++;
        if (isset($pathElements[$index])) {
            $tree[$pathElements[$currentIndex]]['children'] = $this->explodePathToArray($pathElements, $index);
        }
        return $tree;
    }

    /**
     * Recursive method to sort tree
     *
     * @param array $tree
     * @return array
     */
    private function sortTree(array $tree): array
    {
        foreach ($tree as &$node) {
            if ($node['children']) {
                uasort($node['children'], function ($element1, $element2) {
                    return ($element1['position'] <=> $element2['position']);
                });
                $node['children'] = $this->sortTree($node['children']);
                if (isset($node['children_count'])) {
                    $node['children_count'] = count($node['children']);
                }
            } elseif (isset($node['children_count'])) {
                $node['children_count'] = 0;
            }
        }

        return $tree;
    }
}
