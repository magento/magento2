<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\ConfigurableProduct\Test\Unit\Model\Product\Type\Configurable\Variations;

use Magento\ConfigurableProduct\Model\Product\Type\Configurable\Variations\Prices;
use Magento\Framework\Locale\Format;
use Magento\Framework\Pricing\Amount\AmountInterface;
use Magento\Framework\Pricing\Price\PriceInterface;
use Magento\Framework\Pricing\PriceInfo\Base;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class PricesTest extends TestCase
{
    /**
     * @var MockObject
     */
    private $localeFormatMock;

    /**
     * @var Prices
     */
    private $model;

    protected function setUp(): void
    {
        $this->localeFormatMock = $this->createMock(Format::class);
        $this->model = new Prices(
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
        $priceInfoMock = $this->createMock(Base::class);
        $priceMock = $this->getMockForAbstractClass(PriceInterface::class);
        $priceInfoMock->expects($this->atLeastOnce())->method('getPrice')->willReturn($priceMock);

        $amountMock = $this->getMockForAbstractClass(AmountInterface::class);
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
