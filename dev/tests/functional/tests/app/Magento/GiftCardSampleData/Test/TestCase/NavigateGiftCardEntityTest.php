<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GiftCardSampleData\Test\TestCase;

use Magento\Mtf\TestCase\Injectable;
use Magento\GiftCard\Test\Fixture\GiftCardProduct;

/**
 * @ZephyrId MAGETWO-33559
 * @group Catalog_Sample_Data(MX)
 */
class NavigateGiftCardEntityTest extends Injectable
{
    /* tags */
    const TEST_TYPE = 'acceptance_test';
    const MVP = 'yes';
    const DOMAIN = 'MX';
    /* end tags */

    /**
     * Run test navigate products
     *
     * @param GiftCardProduct    $product
     * @return array
     */
    public function test(GiftCardProduct $product)
    {
        return ['product' => $product];
    }
}
