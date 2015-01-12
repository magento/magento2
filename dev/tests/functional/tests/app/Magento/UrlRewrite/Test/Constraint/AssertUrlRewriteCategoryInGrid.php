<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\UrlRewrite\Test\Constraint;

use Magento\Catalog\Test\Fixture\CatalogCategory;
use Magento\UrlRewrite\Test\Page\Adminhtml\UrlRewriteIndex;
use Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertUrlRewriteCategoryInGrid
 * Assert that url category in grid
 */
class AssertUrlRewriteCategoryInGrid extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'low';
    /* end tags */

    /**
     * Assert that url rewrite category in grid
     *
     * @param CatalogCategory $category
     * @param UrlRewriteIndex $urlRewriteIndex
     * @return void
     */
    public function processAssert(CatalogCategory $category, UrlRewriteIndex $urlRewriteIndex)
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
