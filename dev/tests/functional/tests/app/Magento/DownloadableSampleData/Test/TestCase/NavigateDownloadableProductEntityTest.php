<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\DownloadableSampleData\Test\TestCase;

use Magento\Mtf\TestCase\Injectable;
use Magento\Downloadable\Test\Fixture\DownloadableProduct;

/**
 * @ZephyrId MAGETWO-33559
 * @group Sample_Data_(MX)
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
     * @param DownloadableProduct $product
     * @return array
     */
    public function test(DownloadableProduct $product)
    {
        return ['product' => $product];
    }
}
