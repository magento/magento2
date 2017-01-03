<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\UrlRewrite\Test\Constraint;

use Magento\Mtf\Fixture\FixtureInterface;
use Magento\UrlRewrite\Test\Page\Adminhtml\UrlRewriteIndex;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert that url rewrite product in grid.
 */
class AssertUrlRewriteProductInGrid extends AbstractConstraint
{
    /**
     * Assert that url rewrite product in grid.
     *
     * @param UrlRewriteIndex $urlRewriteIndex
     * @param FixtureInterface $product
     * @return void
     */
    public function processAssert(
        UrlRewriteIndex $urlRewriteIndex,
        FixtureInterface $product
    ) {
        $urlRewriteIndex->open();
        $categories = $product->getDataFieldConfig('category_ids')['source']->getCategories();
        $rootCategoryArray = [];
        foreach ($categories as $category) {
            $parentName = $category->getDataFieldConfig('parent_id')['source']->getParentCategory()->getName();
            $rootCategoryArray[$parentName] = $category->getUrlKey();
        }

        $stores = $product->getDataFieldConfig('website_ids')['source']->getStores();
        foreach ($stores as $store) {
            $rootCategoryName = $store->getDataFieldConfig('group_id')['source']
                ->getStoreGroup()
                ->getDataFieldConfig('root_category_id')['source']
                ->getCategory()
                ->getName();

            $storeName = $store->getName();
            $filters = [
                [
                    'request_path' => $product->getUrlKey() . '.html',
                    'store_id' => $storeName
                ],
                [
                    'request_path' => $rootCategoryArray[$rootCategoryName] . '.html',
                    'store_id' => $storeName
                ],
                [
                    'request_path' => $rootCategoryArray[$rootCategoryName] . '/' . $product->getUrlKey() . '.html',
                    'store_id' => $storeName
                ],
            ];
            foreach ($filters as $filter) {
                \PHPUnit_Framework_Assert::assertTrue(
                    $urlRewriteIndex->getUrlRedirectGrid()->isRowVisible($filter, true, false),
                    'URL Rewrite with request path \'' . $filter['request_path'] . '\' is absent in grid.'
                );

            }
        }
    }

    /**
     * URL rewrite product present in grid.
     *
     * @return string
     */
    public function toString()
    {
        return 'URL Rewrite is present in grid.';
    }
}
