<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\DownloadableSampleData\Test\TestCase;

use Magento\Mtf\Client\BrowserInterface;
use Magento\Mtf\TestCase\Injectable;
use Magento\Downloadable\Test\Fixture\DownloadableProduct;
use Magento\Catalog\Test\Page\Product\CatalogProductView;

/**
 * @ZephyrId MAGETWO-33559
 * @group Catalog_Sample_Data(MX)
 */
class NavigateDownloadableProductEntityTest extends Injectable
{
    /* tags */
    const TEST_TYPE = 'acceptance_test';
    const MVP = 'yes';
    const DOMAIN = 'MX';
    /* end tags */

    /**
     * Run test navigate products.
     *
     * @return void
     */
    public function test(DownloadableProduct $product, CatalogProductView $productView, BrowserInterface $browser)
    {
        $browser->open($_ENV['app_frontend_url'] . $product->getUrlKey() . '.html');
        \PHPUnit_Framework_Assert::assertTrue(
            $productView->getViewBlock()->isGalleryVisible(),
            'Gallery for product ' . $product->getName() . ' is not visible.'
        );

        return ['product' => $product];
    }
}
