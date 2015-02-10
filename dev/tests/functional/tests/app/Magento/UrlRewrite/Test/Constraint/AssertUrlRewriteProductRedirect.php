<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\UrlRewrite\Test\Constraint;

use Magento\Catalog\Test\Page\Product\CatalogProductView;
use Magento\UrlRewrite\Test\Fixture\UrlRewrite;
use Magento\Mtf\Client\BrowserInterface;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Mtf\Fixture\InjectableFixture;

/**
 * Class AssertUrlRewriteProductRedirect
 * Assert that product available by new URL on the front
 */
class AssertUrlRewriteProductRedirect extends AbstractConstraint
{
    /**
     * Check that product available by new URL on the front
     *
     * @param UrlRewrite $urlRewrite
     * @param CatalogProductView $catalogProductView
     * @param BrowserInterface $browser
     * @param InjectableFixture $product
     * @return void
     */
    public function processAssert(
        UrlRewrite $urlRewrite,
        CatalogProductView $catalogProductView,
        BrowserInterface $browser,
        InjectableFixture $product = null
    ) {
        $browser->open($_ENV['app_frontend_url'] . $urlRewrite->getRequestPath());
        if ($product === null) {
            $product = $urlRewrite->getDataFieldConfig('target_path')['source']->getEntity();
        }
        \PHPUnit_Framework_Assert::assertEquals(
            $catalogProductView->getTitleBlock()->getTitle(),
            $product->getName(),
            'URL rewrite product redirect false.'
            . "\nExpected: " . $product->getName()
            . "\nActual: " . $catalogProductView->getTitleBlock()->getTitle()
        );
    }

    /**
     * Product available by new URL on the front
     *
     * @return string
     */
    public function toString()
    {
        return 'Product available by new URL on the front.';
    }
}
