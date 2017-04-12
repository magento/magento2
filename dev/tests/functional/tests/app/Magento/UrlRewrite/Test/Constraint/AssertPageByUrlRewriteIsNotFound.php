<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\UrlRewrite\Test\Constraint;

use Magento\Catalog\Test\Page\Product\CatalogProductView;
use Magento\UrlRewrite\Test\Fixture\UrlRewrite;
use Magento\Mtf\Client\BrowserInterface;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertPageByUrlRewriteIsNotFound
 * Checking the server response 404 page on frontend
 */
class AssertPageByUrlRewriteIsNotFound extends AbstractConstraint
{
    /**
     * Message on the product page 404
     */
    const NOT_FOUND_MESSAGE = 'Whoops, our bad...';

    /**
     * Checking the server response 404 page on frontend
     *
     * @param BrowserInterface $browser
     * @param CatalogProductView $catalogProductView
     * @param UrlRewrite $productRedirect
     * @return void
     */
    public function processAssert(
        BrowserInterface $browser,
        CatalogProductView $catalogProductView,
        UrlRewrite $productRedirect
    ) {
        $browser->open($_ENV['app_frontend_url'] . $productRedirect->getRequestPath());
        \PHPUnit_Framework_Assert::assertEquals(
            self::NOT_FOUND_MESSAGE,
            $catalogProductView->getTitleBlock()->getTitle(),
            'Wrong page is displayed.'
        );
    }

    /**
     * Not found page is display
     *
     * @return string
     */
    public function toString()
    {
        return 'Not found page is display.';
    }
}
