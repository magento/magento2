<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\UrlRewrite\Test\Constraint;

use Magento\UrlRewrite\Test\Fixture\UrlRewrite;
use Magento\UrlRewrite\Test\Page\Adminhtml\UrlRewriteIndex;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertUrlRewriteNotInGrid
 * Assert that url rewrite category not in grid
 */
class AssertUrlRewriteNotInGrid extends AbstractConstraint
{
    /**
     * Assert that url rewrite not in grid
     *
     * @param UrlRewriteIndex $urlRewriteIndex
     * @param UrlRewrite $productRedirect
     * @return void
     */
    public function processAssert(UrlRewriteIndex $urlRewriteIndex, UrlRewrite $productRedirect)
    {
        $urlRewriteIndex->open();
        $filter = ['request_path' => $productRedirect->getRequestPath()];
        \PHPUnit_Framework_Assert::assertFalse(
            $urlRewriteIndex->getUrlRedirectGrid()->isRowVisible($filter),
            'URL Rewrite with request path \'' . $productRedirect->getRequestPath() . '\' is present in grid.'
        );
    }

    /**
     * URL rewrite category not present in grid
     *
     * @return string
     */
    public function toString()
    {
        return 'URL Rewrite is not present in grid.';
    }
}
