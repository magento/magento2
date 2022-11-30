<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider;

use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\ResourceModel\Category\Collection;
use Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\CategoryTree\Wrapper\Forgery;

class ExtractDataFromCategoryTree
{
    /**
     * Build result tree from collection
     *
     * @param Collection $collection
     * @param array $topLevelCategories
     * @return array
     */
    public function buildTree(Collection $collection, array $topLevelCategories) : array
    {
        $forgery = new Forgery();
        /** @var Category $item */
        foreach ($collection->getItems() as $item) {
            $forgery->forge($item);
        }
        $tree = [];
        foreach ($topLevelCategories as $topLevelCategory) {
            $tree[] = $forgery->getNodeById($topLevelCategory)->renderArray();
        }
        return $this->sortTree($tree);
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
