<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
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
        $configurableProduct = $this->createPartialMock(
            Product::class,
            ['getCustomOption', 'getPriceInfo', 'setFinalPrice']
        );
        /** @var PriceInfoBase|MockObject $priceInfo */
        $priceInfo = $this->createPartialMock(PriceInfoBase::class, ['getPrice']);
        /** @var PriceInterface|MockObject $price */
        $price = $this->createMock(PriceInterface::class);
        /** @var AmountInterface|MockObject $amount */
        $amount = $this->createMock(AmountInterface::class);

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
        $configurableProduct = new \Magento\Catalog\Test\Unit\Helper\ProductTestHelper();
        /** @var Option|MockObject $customOption */
        // Use parent Option class - setProduct and getProduct work via DataObject magic methods
        $customOption = $this->createPartialMock(Option::class, []);
        /** @var Product|MockObject $simpleProduct */
        $simpleProduct = new \Magento\Catalog\Test\Unit\Helper\ProductTestHelper();

        // Configure helpers with expected values
        $configurableProduct->setCustomOption('simple_product', $customOption);
        $configurableProduct->setCustomOption('option_ids', false);
        $configurableProduct->setCustomerGroupId($customerGroupId);

        $customOption->setProduct($simpleProduct);

        $simpleProduct->setCustomerGroupId($customerGroupId);
        $simpleProduct->setPrice($finalPrice);
        $simpleProduct->setData('final_price', $finalPrice);
        $simpleProduct->setCustomOption('option_ids', false);
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
