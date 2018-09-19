<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\Test\Unit\Model\Product\Type\Configurable;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class PriceTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable\Price */
    protected $model;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    protected function setUp()
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $this->model = $this->objectManagerHelper->getObject(
            \Magento\ConfigurableProduct\Model\Product\Type\Configurable\Price::class
        );
    }

    public function testGetFinalPrice()
    {
        $finalPrice = 10;
        $qty = 1;
        $configurableProduct = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCustomOption', 'getPriceInfo', 'setFinalPrice', '__wakeUp'])
            ->getMock();
        $customOption = $this->getMockBuilder(\Magento\Catalog\Model\Product\Configuration\Item\Option::class)
            ->disableOriginalConstructor()
            ->setMethods(['getProduct'])
            ->getMock();
        $priceInfo = $this->getMockBuilder(\Magento\Framework\Pricing\PriceInfo\Base::class)
            ->disableOriginalConstructor()
            ->setMethods(['getPrice'])
            ->getMock();
        $price = $this->getMockBuilder(\Magento\Framework\Pricing\Price\PriceInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $amount = $this->getMockBuilder(\Magento\Framework\Pricing\Amount\AmountInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $configurableProduct->expects($this->any())
            ->method('getCustomOption')
            ->willReturnMap([['simple_product', false], ['option_ids', false]]);
        $customOption->expects($this->never())->method('getProduct');
        $configurableProduct->expects($this->once())->method('getPriceInfo')->willReturn($priceInfo);
        $priceInfo->expects($this->once())->method('getPrice')->with('final_price')->willReturn($price);
        $price->expects($this->once())->method('getAmount')->willReturn($amount);
        $amount->expects($this->once())->method('getValue')->willReturn($finalPrice);
        $configurableProduct->expects($this->once())->method('setFinalPrice')->with($finalPrice)->willReturnSelf();

        $this->assertEquals($finalPrice, $this->model->getFinalPrice($qty, $configurableProduct));
    }
}
