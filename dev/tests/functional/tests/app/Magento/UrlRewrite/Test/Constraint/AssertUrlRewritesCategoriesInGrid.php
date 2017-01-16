<?php
/**
 * Copyright © 2013-2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\UrlRewrite\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\UrlRewrite\Test\Page\Adminhtml\UrlRewriteIndex;
use Magento\Mtf\Util\Protocol\CurlTransport\WebapiDecorator;

/**
 * Assert that category url rewrites are present in grid.
 */
class AssertUrlRewritesCategoriesInGrid extends AbstractConstraint
{
    /**
     * Assert that category url rewrites are present in grid.
     *
     * @param AssertUrlRewriteCategoryInGrid $assertUrlRewriteCategoryInGrid
     * @param WebapiDecorator $webApi
     * @param UrlRewriteIndex $urlRewriteIndex
     * @param array $categories
     * @param string $filterByPath [optional]
     * @param string $redirectType [optional]
     * @return void
     */
    public function processAssert(
        AssertUrlRewriteCategoryInGrid $assertUrlRewriteCategoryInGrid,
        WebapiDecorator $webApi,
        UrlRewriteIndex $urlRewriteIndex,
        array $categories,
        $filterByPath = 'target_path',
        $redirectType = 'Permanent (301)'
    ) {
        foreach ($categories as $key => $category) {
            $assertUrlRewriteCategoryInGrid->processAssert(
                $category,
                $webApi,
                $urlRewriteIndex,
                null,
                null,
                $key,
                $filterByPath,
                $redirectType
            );
        }
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Category url rewrites are present in grid.';
    }
}
