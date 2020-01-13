<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ProductVideo\Test\Constraint;

use Magento\Mtf\Client\BrowserInterface;
use Magento\Mtf\Fixture\InjectableFixture;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Catalog\Test\Page\Product\CatalogProductView;

/**
 * Assert that video is displayed on product page.
 */
class AssertVideoConfigurableProductView extends AbstractConstraint
{
    /**
     * Assert that video is displayed on product page on Store front.
     *
     * @param BrowserInterface $browser
     * @param CatalogProductView $catalogProductView
     * @param InjectableFixture $product
     * @param string $youtubeDataCode
     * @param string $vimeoDataCode
     * @param string $variation
     */
    public function processAssert(
        BrowserInterface $browser,
        CatalogProductView $catalogProductView,
        InjectableFixture $product,
        $youtubeDataCode,
        $vimeoDataCode,
        $variation
    ) {
        //open product page
        $browser->open($_ENV['app_frontend_url'] . $product->getUrlKey() . '.html');
        // assert video and video data of configurable product is presented on page
        \PHPUnit\Framework\Assert::assertTrue(
            $catalogProductView->getViewBlock()->isVideoVisible(),
            'Product video is not displayed on product view when it should.'
        );
        \PHPUnit\Framework\Assert::assertTrue(
            $catalogProductView->getViewBlock()->checkVideoDataPresence($youtubeDataCode),
            'Configurable product video data is not displayed on product view when it should.'
        );
       // select configurable product variation
        $catalogProductView->getConfigurableAttributesBlock()->selectConfigurableOption($product, $variation);
        // assert video and video data of simple product option is presented on page
        \PHPUnit\Framework\Assert::assertTrue(
            $catalogProductView->getViewBlock()->isVideoVisible(),
            'Configurable product variation video is not displayed on product view when it should.'
        );
        \PHPUnit\Framework\Assert::assertTrue(
            $catalogProductView->getViewBlock()->checkVideoDataPresence($vimeoDataCode),
            'Configurable product variation video data is not displayed on product view when it should.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Configurable product video and it variation video are displayed on product view.';
    }
}
