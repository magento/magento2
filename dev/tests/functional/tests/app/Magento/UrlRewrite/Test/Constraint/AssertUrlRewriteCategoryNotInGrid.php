<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\UrlRewrite\Test\Constraint;

use Magento\Catalog\Test\Fixture\Category;
use Magento\UrlRewrite\Test\Page\Adminhtml\UrlRewriteIndex;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertUrlRewriteCategoryNotInGrid
 * Assert that url rewrite category is absent in grid
 */
class AssertUrlRewriteCategoryNotInGrid extends AbstractConstraint
{
    /**
     * Assert that category url rewrite not in grid
     *
     * @param UrlRewriteIndex $urlRewriteIndex
     * @param Category $category
     * @return void
     */
    public function processAssert(UrlRewriteIndex $urlRewriteIndex, Category $category)
    {
        $urlRewriteIndex->open();
        $filter = ['request_path' => $category->getUrlKey()];
        \PHPUnit_Framework_Assert::assertFalse(
            $urlRewriteIndex->getUrlRedirectGrid()->isRowVisible($filter),
            "URL Rewrite with request path '{$category->getUrlKey()}' is present in grid."
        );
    }

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function toString()
    {
        return 'URL Rewrite is absent in grid.';
    }
}
