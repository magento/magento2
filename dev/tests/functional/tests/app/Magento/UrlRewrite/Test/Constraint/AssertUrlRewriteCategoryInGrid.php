<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\UrlRewrite\Test\Constraint;

use Magento\Catalog\Test\Fixture\Category;
use Magento\UrlRewrite\Test\Page\Adminhtml\UrlRewriteIndex;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertUrlRewriteCategoryInGrid
 * Assert that url category in grid
 */
class AssertUrlRewriteCategoryInGrid extends AbstractConstraint
{
    /**
     * Assert that url rewrite category in grid
     *
     * @param Category $category
     * @param UrlRewriteIndex $urlRewriteIndex
     * @return void
     */
    public function processAssert(Category $category, UrlRewriteIndex $urlRewriteIndex)
    {
        $urlRewriteIndex->open();
        $filter = ['target_path' => strtolower($category->getUrlKey())];
        \PHPUnit_Framework_Assert::assertTrue(
            $urlRewriteIndex->getUrlRedirectGrid()->isRowVisible($filter, true, false),
            'URL Rewrite with request path "' . $category->getUrlKey() . '" is absent in grid.'
        );
    }

    /**
     * URL rewrite category present in grid
     *
     * @return string
     */
    public function toString()
    {
        return 'URL Rewrite is present in grid.';
    }
}
