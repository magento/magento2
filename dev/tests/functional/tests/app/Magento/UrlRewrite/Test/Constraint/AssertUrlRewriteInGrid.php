<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\UrlRewrite\Test\Constraint;

use Magento\UrlRewrite\Test\Fixture\UrlRewrite;
use Magento\UrlRewrite\Test\Page\Adminhtml\UrlRewriteIndex;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertUrlRewriteInGrid
 * Assert that url rewrite category in grid
 */
class AssertUrlRewriteInGrid extends AbstractConstraint
{
    /**
     * Assert that url rewrite category in grid
     *
     * @param UrlRewriteIndex $urlRewriteIndex
     * @param UrlRewrite $urlRewrite
     * @return void
     */
    public function processAssert(UrlRewriteIndex $urlRewriteIndex, UrlRewrite $urlRewrite)
    {
        $urlRewriteIndex->open();
        $filter = ['request_path' => $urlRewrite->getRequestPath()];
        \PHPUnit\Framework\Assert::assertTrue(
            $urlRewriteIndex->getUrlRedirectGrid()->isRowVisible($filter),
            'URL Rewrite with request path \'' . $urlRewrite->getRequestPath() . '\' is absent in grid.'
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
