<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\UrlRewrite\Test\Constraint;

use Magento\Mtf\Fixture\FixtureInterface;
use Magento\UrlRewrite\Test\Page\Adminhtml\UrlRewriteIndex;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert that url rewrite product not in grid.
 */
class AssertUrlRewriteProductNotInGrid extends AbstractConstraint
{
    /**
     * Assert that url rewrite not in grid
     *
     * @param UrlRewriteIndex $urlRewriteIndex
     * @param FixtureInterface $product
     * @return void
     */
    public function processAssert(UrlRewriteIndex $urlRewriteIndex, FixtureInterface $product)
    {
        $urlRewriteIndex->open();
        $requestPath = $product->getUrlKey() . '.html';
        $filter = ['request_path' => $requestPath];
        \PHPUnit\Framework\Assert::assertFalse(
            $urlRewriteIndex->getUrlRedirectGrid()->isRowVisible($filter),
            'URL Rewrite with request path \'' . $requestPath . '\' is present in grid.'
        );
    }

    /**
     * URL rewrite product not present in grid.
     *
     * @return string
     */
    public function toString()
    {
        return 'URL Rewrite is not present in grid.';
    }
}
