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

    /**
     * @inheritDoc
     */
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
     * @return void
     * @dataProvider showRangePriceDataProvider
     */
    public function testShowRangePrice(
        $optMinValue,
        $optMaxValue,
        $custMinValue,
        $custMaxValue,
        $expectedShowRange
    ): void {
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

        $priceWithArgs = $priceWillReturnArgs = [];
        $priceWithArgs[] = [FinalPrice::PRICE_CODE];
        $priceWillReturnArgs[] = $bundlePrice;

        if ($enableCustomOptionMocks) {
            $priceWithArgs[] = [CustomOptionPrice::PRICE_CODE];
            $priceWillReturnArgs[] = $customOptionPrice;
        }
        $priceInfo
            ->method('getPrice')
            ->willReturnCallback(function ($priceWithArgs) use ($priceWillReturnArgs) {
                static $callCount = 0;
                $returnValue = $priceWillReturnArgs[$callCount] ?? null;
                $callCount++;
                return $returnValue;
            });

        $bundlePrice->expects($this->once())
            ->method('getMinimalPrice')
            ->willReturn($optMinValue);
        $bundlePrice->expects($this->once())
            ->method('getMaximalPrice')
            ->willReturn($optMaxValue);

        if ($enableCustomOptionMocks) {
            $customOptionPrice
                ->method('getCustomOptionRange')
                ->willReturnOnConsecutiveCalls($custMinValue, $custMaxValue);
        }

        $this->assertEquals($expectedShowRange, $this->model->showRangePrice());
    }

    /**
     * @return array
     */
    public static function showRangePriceDataProvider(): array
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
            ]
        ];
    }
}
