<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider;

use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\ResourceModel\Category\Collection;
use Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\CategoryTree\Wrapper\NodeWrapperFactory;

/**
 * Data extractor for category tree processing in GraphQL resolvers.
 */
class ExtractDataFromCategoryTree
{
    /**
     * @var NodeWrapperFactory
     */
    private $nodeWrapperFactory;

    /**
     * @param NodeWrapperFactory $nodeWrapperFactory
     */
    public function __construct(NodeWrapperFactory $nodeWrapperFactory)
    {
        $this->nodeWrapperFactory = $nodeWrapperFactory;
    }

    /**
     * Build result tree from collection
     *
     * @param Collection $collection
     * @param array $topLevelCategoryIds
     * @return array
     */
    public function buildTree(Collection $collection, array $topLevelCategoryIds) : array
    {
        $wrapper = $this->nodeWrapperFactory->create();
        /** @var Category $item */
        foreach ($collection->getItems() as $item) {
            $wrapper->wrap($item);
        }
        $tree = [];
        foreach ($topLevelCategoryIds as $topLevelCategory) {
            $tree[] = $wrapper->getNodeById($topLevelCategory)->renderArray();
        }
        return $this->sortTree($tree);
    }

    /**
     * Recursive method to sort tree
     *
     * @param array $tree
     * @return array
     */
    private function sortTree(array &$tree): array
    {
        foreach ($tree as &$node) {
            if (!empty($node['children'])) {
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
            // redirect_code null will not return , so it will be 0 when there is no redirect error.
            if (!isset($node['redirect_code'])) {
                $node['redirect_code'] = 0;
            }
        }

        return $tree;
    }
}
