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
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;

class PriceTest extends TestCase
{
    use MockCreationTrait;
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
        $basePrice = 10;

        /** @var Product|MockObject $configurableProduct */
        $configurableProduct = $this->createPartialMockWithReflection(
            Product::class,
            ['getCustomOption', 'getCustomerGroupId', 'setFinalPrice', 'getCalculatedFinalPrice']
        );
        
        /** @var Option|MockObject $customOption */
        $customOption = $this->createPartialMockWithReflection(
            Option::class,
            ['getProduct']
        );
        
        /** @var Product|MockObject $simpleProduct */
        $simpleProduct = $this->createPartialMockWithReflection(
            Product::class,
            [
                'setCustomerGroupId',
                'getPrice',
                'getTierPrice',
                'getSpecialPrice',
                'getSpecialFromDate',
                'getSpecialToDate',
                'setFinalPrice',
                'getData',
                'getCustomOption',
                'getCalculatedFinalPrice'
            ]
        );

        // Configure configurable product mock
        $configurableProduct->method('getCustomOption')
            ->willReturnMap([
                ['simple_product', $customOption],
                ['option_ids', false]
            ]);
        $configurableProduct->method('getCustomerGroupId')->willReturn($customerGroupId);
        $configurableProduct->method('getCalculatedFinalPrice')->willReturn(null);
        $configurableProduct->expects($this->once())->method('setFinalPrice')->with($finalPrice)->willReturnSelf();
        
        // Configure custom option mock
        $customOption->method('getProduct')->willReturn($simpleProduct);
        
        // Configure simple product mock for parent::getFinalPrice() call
        // getBasePrice() calls getPrice(), getTierPrice(), getSpecialPrice()
        $simpleProduct->expects($this->once())->method('setCustomerGroupId')->with($customerGroupId)->willReturnSelf();
        $simpleProduct->method('getPrice')->willReturn($basePrice);
        $simpleProduct->method('getTierPrice')->willReturn(null);
        $simpleProduct->method('getSpecialPrice')->willReturn(null);
        $simpleProduct->method('getSpecialFromDate')->willReturn(null);
        $simpleProduct->method('getSpecialToDate')->willReturn(null);
        $simpleProduct->method('getCalculatedFinalPrice')->willReturn(null);
        
        // getFinalPrice() sets final price, then gets it back via getData('final_price')
        // Make the mock stateful so setFinalPrice() and getData() work together
        $finalPriceValue = null;
        $simpleProduct->method('setFinalPrice')->willReturnCallback(
            function ($price) use (&$finalPriceValue, $simpleProduct) {
                $finalPriceValue = $price;
                return $simpleProduct;
            }
        );
        $simpleProduct->method('getData')->willReturnCallback(
            function ($key = null) use (&$finalPriceValue, $basePrice) {
                if ($key === 'final_price') {
                    return $finalPriceValue;
                } elseif ($key === 'price') {
                    return $basePrice;
                }
                return null;
            }
        );
        $simpleProduct->method('getCustomOption')->with('option_ids')->willReturn(false);
        
        // Verify event dispatch for simple product
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
