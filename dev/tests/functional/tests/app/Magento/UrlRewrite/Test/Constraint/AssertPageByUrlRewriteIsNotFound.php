<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\UrlRewrite\Test\Constraint;

use Magento\Catalog\Test\Page\Product\CatalogProductView;
use Magento\UrlRewrite\Test\Fixture\UrlRewrite;
use Mtf\Client\Browser;
use Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertPageByUrlRewriteIsNotFound
 * Checking the server response 404 page on frontend
 */
class AssertPageByUrlRewriteIsNotFound extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'low';
    /* end tags */

    /**
     * Message on the product page 404
     */
    const NOT_FOUND_MESSAGE = 'Whoops, our bad...';

    /**
     * Checking the server response 404 page on frontend
     *
     * @param Browser $browser
     * @param CatalogProductView $catalogProductView
     * @param UrlRewrite $productRedirect
     * @return void
     */
    public function processAssert(
        Browser $browser,
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
