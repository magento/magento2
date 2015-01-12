<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\UrlRewrite\Test\Constraint;

use Magento\Catalog\Test\Fixture\CatalogCategory;
use Magento\UrlRewrite\Test\Fixture\UrlRewrite;
use Mtf\Client\Browser;
use Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertUrlRewriteCategoryRedirect
 * Assert check URL rewrite category redirect
 */
class AssertUrlRewriteCategoryRedirect extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'low';
    /* end tags */

    /**
     * Assert check URL rewrite category redirect
     *
     * @param UrlRewrite $urlRewrite
     * @param CatalogCategory $category
     * @param Browser $browser
     * @return void
     */
    public function processAssert(
        UrlRewrite $urlRewrite,
        CatalogCategory $category,
        Browser $browser
    ) {
        $browser->open($_ENV['app_frontend_url'] . $urlRewrite->getRequestPath());
        $url = $urlRewrite->getRedirectType() == 'No'
            ? $urlRewrite->getRequestPath()
            : $category->getUrlKey() . '.html';

        \PHPUnit_Framework_Assert::assertEquals(
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
