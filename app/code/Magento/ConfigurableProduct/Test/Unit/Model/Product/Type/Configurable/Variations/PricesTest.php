<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\ConfigurableProduct\Test\Unit\Model\Product\Type\Configurable\Variations;

use PHPUnit\Framework\TestCase;

class PricesTest extends TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $localeFormatMock;

    /**
     * @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable\Variations\Prices
     */
    private $model;

    protected function setUp()
    {
        $this->localeFormatMock = $this->createMock(\Magento\Framework\Locale\Format::class);
        $this->model = new \Magento\ConfigurableProduct\Model\Product\Type\Configurable\Variations\Prices(
            $this->localeFormatMock
        );
    }

    public function testGetFormattedPrices()
    {
        $expected = [
            'oldPrice' => [
                'amount' => 500
            ],
            'basePrice' => [
                'amount' => 1000
            ],
            'finalPrice' => [
                'amount' => 500
            ]
        ];
        $priceInfoMock = $this->createMock(\Magento\Framework\Pricing\PriceInfo\Base::class);
        $priceMock = $this->createMock(\Magento\Framework\Pricing\Price\PriceInterface::class);
        $priceInfoMock->expects($this->atLeastOnce())->method('getPrice')->willReturn($priceMock);

        $amountMock = $this->createMock(\Magento\Framework\Pricing\Amount\AmountInterface::class);
        $amountMock->expects($this->atLeastOnce())->method('getValue')->willReturn(500);
        $amountMock->expects($this->atLeastOnce())->method('getBaseAmount')->willReturn(1000);
        $priceMock->expects($this->atLeastOnce())->method('getAmount')->willReturn($amountMock);

        $this->localeFormatMock->expects($this->atLeastOnce())
            ->method('getNumber')
            ->withConsecutive([500], [1000], [500])
            ->will($this->onConsecutiveCalls(500, 1000, 500));

        $this->assertEquals($expected, $this->model->getFormattedPrices($priceInfoMock));
    }
}
