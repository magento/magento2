<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Test\Unit\Model\Resolver\Product\Price;

use Magento\CatalogGraphQl\Model\Resolver\Product\Price\Discount;
use PHPUnit\Framework\TestCase;

class DiscountTest extends TestCase
{
    /**
     * @var Discount
     */
    private $discount;

    protected function setUp(): void
    {
        $this->discount = new Discount();
    }

    /**
     * @dataProvider priceDataProvider
     * @param $regularPrice
     * @param $finalPrice
     * @param $expectedAmountOff
     * @param $expectedPercentOff
     */
    public function testGetPriceDiscount($regularPrice, $finalPrice, $expectedAmountOff, $expectedPercentOff)
    {
        $discountResult = $this->discount->getDiscountByDifference($regularPrice, $finalPrice);

        $this->assertEquals($expectedAmountOff, $discountResult['amount_off']);
        $this->assertEquals($expectedPercentOff, $discountResult['percent_off']);
    }

    /**
     * Price data provider
     *
     * [regularPrice, finalPrice, expectedAmountOff, expectedPercentOff]
     *
     * @return array
     */
    public function priceDataProvider()
    {
        return [
            [100, 50, 50, 50],
            [.1, .05, .05, 50],
            [12.50, 10, 2.5, 20],
            [99.99, 84.99, 15.0, 15],
            [9999999999.01, 8999999999.11, 999999999.9, 10],
            [0, 0, 0, 0],
            [0, 10, 0, 0],
            [9.95, 9.95, 0, 0],
            [21.05, 0, 21.05, 100]
        ];
    }
}
