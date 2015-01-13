<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Pricing\Render;

use Magento\Bundle\Pricing\Price;

class FinalPriceBoxTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var FinalPriceBox
     */
    protected $model;

    /**
     * @var \Magento\Framework\Pricing\Object\SaleableInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $saleableItem;

    public function setUp()
    {
        $this->saleableItem = $this->getMock('Magento\Framework\Pricing\Object\SaleableInterface');

        $objectHelper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->model = $objectHelper->getObject('Magento\Bundle\Pricing\Render\FinalPriceBox', [
            'saleableItem' => $this->saleableItem
        ]);
    }

    /**
     * @dataProvider showRangePriceDataProvider
     */
    public function testShowRangePrice($value, $maxValue, $result)
    {
        $priceInfo = $this->getMock('Magento\Framework\Pricing\PriceInfo\Base', [], [], '', false);
        $optionPrice = $this->getMockBuilder('Magento\Bundle\Pricing\Price\BundleOptionPrice')
            ->disableOriginalConstructor()
            ->getMock();

        $this->saleableItem->expects($this->atLeastOnce())
            ->method('getPriceInfo')
            ->will($this->returnValue($priceInfo));

        $priceInfo->expects($this->atLeastOnce())
            ->method('getPrice')
            ->with(Price\BundleOptionPrice::PRICE_CODE)
            ->will($this->returnValue($optionPrice));

        $optionPrice->expects($this->once())
            ->method('getValue')
            ->will($this->returnValue($value));

        $optionPrice->expects($this->once())
            ->method('getMaxValue')
            ->will($this->returnValue($maxValue));

        $this->assertEquals($result, $this->model->showRangePrice());
    }

    /**
     * @return array
     */
    public function showRangePriceDataProvider()
    {
        return [
            ['value' => 40.2, 'maxValue' => 45., 'result' => true],
            ['value' => false, 'maxValue' => false, 'result' => false],
            ['value' => 45.0, 'maxValue' => 45., 'result' => false],
        ];
    }
}
