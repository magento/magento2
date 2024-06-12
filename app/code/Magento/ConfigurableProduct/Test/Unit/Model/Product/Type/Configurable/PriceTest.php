<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Test\Unit\Model\Product\Type\Configurable;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Configuration\Item\Option;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable\Price as ConfigurablePrice;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Pricing\Amount\AmountInterface;
use Magento\Framework\Pricing\Price\PriceInterface;
use Magento\Framework\Pricing\PriceInfo\Base as PriceInfoBase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class PriceTest extends TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    /**
     * @var ConfigurablePrice
     */
    protected $model;

    /**
     * @var ManagerInterface|MockObject
     */
    private $eventManagerMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $this->eventManagerMock = $this->createPartialMock(
            ManagerInterface::class,
            ['dispatch']
        );
        $this->model = $this->objectManagerHelper->getObject(
            ConfigurablePrice::class,
            ['eventManager' => $this->eventManagerMock]
        );
    }

    public function testGetFinalPrice()
    {
        $finalPrice = 10;
        $qty = 1;

        /** @var Product|MockObject $configurableProduct */
        $configurableProduct = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getCustomOption', 'getPriceInfo', 'setFinalPrice'])
            ->getMock();
        /** @var PriceInfoBase|MockObject $priceInfo */
        $priceInfo = $this->getMockBuilder(PriceInfoBase::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getPrice'])
            ->getMock();
        /** @var PriceInterface|MockObject $price */
        $price = $this->getMockBuilder(PriceInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        /** @var AmountInterface|MockObject $amount */
        $amount = $this->getMockBuilder(AmountInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $configurableProduct->expects($this->any())
            ->method('getCustomOption')
            ->willReturnMap([['simple_product', false], ['option_ids', false]]);
        $configurableProduct->expects($this->once())->method('getPriceInfo')->willReturn($priceInfo);
        $priceInfo->expects($this->once())->method('getPrice')->with('final_price')->willReturn($price);
        $price->expects($this->once())->method('getAmount')->willReturn($amount);
        $amount->expects($this->once())->method('getValue')->willReturn($finalPrice);
        $configurableProduct->expects($this->once())->method('setFinalPrice')->with($finalPrice)->willReturnSelf();

        $this->assertEquals($finalPrice, $this->model->getFinalPrice($qty, $configurableProduct));
    }

    public function testGetFinalPriceWithSimpleProduct()
    {
        $finalPrice = 10;
        $qty = 1;
        $customerGroupId = 1;

        /** @var Product|MockObject $configurableProduct */
        $configurableProduct = $this->getMockBuilder(Product::class)
            ->addMethods(['getCustomerGroupId'])
            ->onlyMethods(['getCustomOption', 'setFinalPrice'])
            ->disableOriginalConstructor()
            ->getMock();
        /** @var Option|MockObject $customOption */
        $customOption = $this->getMockBuilder(Option::class)
            ->addMethods(['getProduct'])
            ->disableOriginalConstructor()
            ->getMock();
        /** @var Product|MockObject $simpleProduct */
        $simpleProduct = $this->getMockBuilder(Product::class)
            ->addMethods(['setCustomerGroupId'])
            ->onlyMethods(['setFinalPrice', 'getPrice', 'getTierPrice', 'getData', 'getCustomOption'])
            ->disableOriginalConstructor()
            ->getMock();

        $configurableProduct->method('getCustomOption')
            ->willReturnMap([
                ['simple_product', $customOption],
                ['option_ids', false]
            ]);
        $configurableProduct->method('getCustomerGroupId')->willReturn($customerGroupId);
        $configurableProduct->expects($this->atLeastOnce())
            ->method('setFinalPrice')
            ->with($finalPrice)
            ->willReturnSelf();
        $customOption->method('getProduct')->willReturn($simpleProduct);
        $simpleProduct->expects($this->atLeastOnce())
            ->method('setCustomerGroupId')
            ->with($customerGroupId)
            ->willReturnSelf();
        $simpleProduct->method('getPrice')->willReturn($finalPrice);
        $simpleProduct->method('getTierPrice')->with($qty)->willReturn($finalPrice);
        $simpleProduct->expects($this->atLeastOnce())
            ->method('setFinalPrice')
            ->with($finalPrice)
            ->willReturnSelf();
        $simpleProduct->method('getData')->with('final_price')->willReturn($finalPrice);
        $simpleProduct->method('getCustomOption')->with('option_ids')->willReturn(false);
        $this->eventManagerMock->expects($this->once())
            ->method('dispatch')
            ->with('catalog_product_get_final_price', ['product' => $simpleProduct, 'qty' => $qty]);

        $this->assertEquals(
            $finalPrice,
            $this->model->getFinalPrice($qty, $configurableProduct),
            'The final price calculation is wrong'
        );
    }
}
