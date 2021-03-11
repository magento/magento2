<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Test\Unit\Pricing\Render;

use Magento\Bundle\Pricing\Render\FinalPriceBox;
use Magento\Catalog\Pricing\Price\CustomOptionPrice;

class FinalPriceBoxTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var FinalPriceBox
     */
    protected $model;

    /**
     * @var \Magento\Framework\Pricing\SaleableInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $saleableItem;

    protected function setUp(): void
    {
        $this->saleableItem = $this->createMock(\Magento\Framework\Pricing\SaleableInterface::class);

        $objectHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $objectHelper->getObject(
            \Magento\Bundle\Pricing\Render\FinalPriceBox::class,
            ['saleableItem' => $this->saleableItem]
        );
    }

    /**
     * @dataProvider showRangePriceDataProvider
     */
    public function testShowRangePrice($optMinValue, $optMaxValue, $custMinValue, $custMaxValue, $expectedShowRange)
    {
        $enableCustomOptionMocks = ($optMinValue == $optMaxValue);

        $priceInfo = $this->createMock(\Magento\Framework\Pricing\PriceInfo\Base::class);
        $bundlePrice = $this->getMockBuilder(\Magento\Bundle\Pricing\Price\FinalPrice::class)
            ->disableOriginalConstructor()
            ->getMock();
        $customOptionPrice = $this->getMockBuilder(\Magento\Catalog\Pricing\Price\CustomOptionPrice::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->saleableItem->expects($this->atLeastOnce())
            ->method('getPriceInfo')
            ->willReturn($priceInfo);

        $priceInfo->expects($this->at(0))
            ->method('getPrice')
            ->with(\Magento\Bundle\Pricing\Price\FinalPrice::PRICE_CODE)
            ->willReturn($bundlePrice);
        if ($enableCustomOptionMocks) {
            $priceInfo->expects($this->at(1))
                ->method('getPrice')
                ->with(CustomOptionPrice::PRICE_CODE)
                ->willReturn($customOptionPrice);
        }

        $bundlePrice->expects($this->once())
            ->method('getMinimalPrice')
            ->willReturn($optMinValue);
        $bundlePrice->expects($this->once())
            ->method('getMaximalPrice')
            ->willReturn($optMaxValue);

        if ($enableCustomOptionMocks) {
            $customOptionPrice->expects($this->at(0))
                ->method('getCustomOptionRange')
                ->willReturn($custMinValue);
            $customOptionPrice->expects($this->at(1))
                ->method('getCustomOptionRange')
                ->willReturn($custMaxValue);
        }

        $this->assertEquals($expectedShowRange, $this->model->showRangePrice());
    }

    /**
     * @return array
     */
    public function showRangePriceDataProvider()
    {
        return [
            'bundle options different, custom options noop' => [
                'optMinValue' => 40.2,
                'optMaxValue' => 45.,
                'custMinValue' => 0,
                'custMaxValue' => 0,
                'expectedShowRange' => true
            ],

            'bundle options same boolean, custom options same boolean' => [
                'optMinValue' => false,
                'optMaxValue' => false,
                'custMinValue' => false,
                'custMaxValue' => false,
                'expectedShowRange' => false
            ],

            'bundle options same numeric, custom options same' => [
                'optMinValue' => 45.0,
                'optMaxValue' => 45,
                'custMinValue' => 1.0,
                'custMaxValue' => 1,
                'expectedShowRange' => false
            ],

            'bundle options same numeric, custom options different' => [
                'optMinValue' => 45.0,
                'optMaxValue' => 45.,
                'custMinValue' => 0,
                'custMaxValue' => 1,
                'expectedShowRange' => true
            ],
        ];
    }
}
