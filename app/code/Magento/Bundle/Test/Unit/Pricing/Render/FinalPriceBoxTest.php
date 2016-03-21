<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Test\Unit\Pricing\Render;

use Magento\Bundle\Pricing\Render\FinalPriceBox;
use Magento\Bundle\Pricing\Price;
use Magento\Catalog\Pricing\Price\CustomOptionPrice;

class FinalPriceBoxTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var FinalPriceBox
     */
    protected $model;

    /**
     * @var \Magento\Framework\Pricing\SaleableInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $saleableItem;

    protected function setUp()
    {
        $this->saleableItem = $this->getMock('Magento\Framework\Pricing\SaleableInterface');

        $objectHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $objectHelper->getObject('Magento\Bundle\Pricing\Render\FinalPriceBox', [
            'saleableItem' => $this->saleableItem
        ]);
    }

    /**
     * @dataProvider showRangePriceDataProvider
     */
    public function testShowRangePrice($optMinValue, $optMaxValue, $custMinValue, $custMaxValue, $expectedShowRange)
    {
        $enableCustomOptionMocks = ($optMinValue == $optMaxValue);

        $priceInfo = $this->getMock('Magento\Framework\Pricing\PriceInfo\Base', [], [], '', false);
        $bundleOptionPrice = $this->getMockBuilder('Magento\Bundle\Pricing\Price\BundleOptionPrice')
            ->disableOriginalConstructor()
            ->getMock();
        $customOptionPrice = $this->getMockBuilder('Magento\Catalog\Pricing\Price\CustomOptionPrice')
            ->disableOriginalConstructor()
            ->getMock();

        $this->saleableItem->expects($this->atLeastOnce())
            ->method('getPriceInfo')
            ->will($this->returnValue($priceInfo));

        $priceInfo->expects($this->at(0))
            ->method('getPrice')
            ->with(Price\BundleOptionPrice::PRICE_CODE)
            ->will($this->returnValue($bundleOptionPrice));
        if ($enableCustomOptionMocks) {
            $priceInfo->expects($this->at(1))
                ->method('getPrice')
                ->with(CustomOptionPrice::PRICE_CODE)
                ->will($this->returnValue($customOptionPrice));
        }

        $bundleOptionPrice->expects($this->once())
            ->method('getValue')
            ->will($this->returnValue($optMinValue));
        $bundleOptionPrice->expects($this->once())
            ->method('getMaxValue')
            ->will($this->returnValue($optMaxValue));

        if ($enableCustomOptionMocks) {
            $customOptionPrice->expects($this->at(0))
                ->method('getCustomOptionRange')
                ->will($this->returnValue($custMinValue));
            $customOptionPrice->expects($this->at(1))
                ->method('getCustomOptionRange')
                ->will($this->returnValue($custMaxValue));
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
