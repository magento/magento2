<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Bundle\Test\Unit\Pricing\Render;

use Magento\Bundle\Pricing\Price\FinalPrice;
use Magento\Bundle\Pricing\Render\FinalPriceBox;
use Magento\Catalog\Pricing\Price\CustomOptionPrice;
use Magento\Framework\Pricing\PriceInfo\Base;
use Magento\Framework\Pricing\SaleableInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class FinalPriceBoxTest extends TestCase
{
    /**
     * @var FinalPriceBox
     */
    protected $model;

    /**
     * @var SaleableInterface|MockObject
     */
    protected $saleableItem;

    protected function setUp(): void
    {
        $this->saleableItem = $this->getMockForAbstractClass(SaleableInterface::class);

        $objectHelper = new ObjectManager($this);
        $this->model = $objectHelper->getObject(
            FinalPriceBox::class,
            ['saleableItem' => $this->saleableItem]
        );
    }

    /**
     * @dataProvider showRangePriceDataProvider
     */
    public function testShowRangePrice($optMinValue, $optMaxValue, $custMinValue, $custMaxValue, $expectedShowRange)
    {
        $enableCustomOptionMocks = ($optMinValue == $optMaxValue);

        $priceInfo = $this->createMock(Base::class);
        $bundlePrice = $this->getMockBuilder(FinalPrice::class)
            ->disableOriginalConstructor()
            ->getMock();
        $customOptionPrice = $this->getMockBuilder(CustomOptionPrice::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->saleableItem->expects($this->atLeastOnce())
            ->method('getPriceInfo')
            ->willReturn($priceInfo);

        $priceInfo->expects($this->at(0))
            ->method('getPrice')
            ->with(FinalPrice::PRICE_CODE)
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
