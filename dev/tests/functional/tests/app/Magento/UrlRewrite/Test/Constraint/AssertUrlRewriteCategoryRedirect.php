<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\UrlRewrite\Test\Constraint;

use Magento\Catalog\Test\Fixture\Category;
use Magento\UrlRewrite\Test\Fixture\UrlRewrite;
use Magento\Mtf\Client\BrowserInterface;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertUrlRewriteCategoryRedirect
 * Assert check URL rewrite category redirect
 */
class AssertUrlRewriteCategoryRedirect extends AbstractConstraint
{
    /**
     * Assert check URL rewrite category redirect
     *
     * @param UrlRewrite $urlRewrite
     * @param Category $category
     * @param BrowserInterface $browser
     * @return void
     */
    public function processAssert(
        UrlRewrite $urlRewrite,
        Category $category,
        BrowserInterface $browser
    ) {
        $browser->open($_ENV['app_frontend_url'] . $urlRewrite->getRequestPath());
        $url = $urlRewrite->getRedirectType() == 'No'
            ? $urlRewrite->getRequestPath()
            : $category->getUrlKey() . '.html';

        \PHPUnit\Framework\Assert::assertEquals(
            $browser->getUrl(),
            $_ENV['app_frontend_url'] . $url,
            'URL rewrite category redirect false.'
            . "\nExpected: " . $_ENV['app_frontend_url'] . $url
            . "\nActual: " . $browser->getUrl()
        );
    }

    /**
     * URL rewrite category redirect success
     *
     * @return string
     */
    public function toString()
    {
        return 'URL rewrite category redirect success.';
    }
}
