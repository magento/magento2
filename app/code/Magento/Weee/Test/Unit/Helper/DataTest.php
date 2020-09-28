<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Weee\Test\Unit\Helper;

use Magento\Bundle\Model\Product\Type;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Type\Simple;
use Magento\Framework\DataObject;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Model\Order\Item;
use Magento\Store\Model\Store;
use Magento\Weee\Helper\Data as WeeeHelper;
use Magento\Weee\Model\Config;
use Magento\Weee\Model\Tax;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DataTest extends TestCase
{
    const ROW_AMOUNT_INVOICED = '200';
    const BASE_ROW_AMOUNT_INVOICED = '400';
    const TAX_AMOUNT_INVOICED = '20';
    const BASE_TAX_AMOUNT_INVOICED = '40';
    const ROW_AMOUNT_REFUNDED = '100';
    const BASE_ROW_AMOUNT_REFUNDED = '201';
    const TAX_AMOUNT_REFUNDED = '10';
    const BASE_TAX_AMOUNT_REFUNDED = '21';

    /**
     * @var Product
     */
    protected $product;

    /**
     * @var Tax
     */
    protected $weeeTax;

    /**
     * @var \Magento\Tax\Helper\Data
     */
    protected $taxData;

    /**
     * @var \Magento\Weee\Helper\Data
     */
    protected $helperData;

    /** @var Json|MockObject */
    private $serializerMock;

    protected function setUp(): void
    {
        $this->product = $this->createMock(Product::class);
        $weeeConfig = $this->createMock(Config::class);
        $weeeConfig->method('isEnabled')->willReturn(true);
        $weeeConfig->method('getListPriceDisplayType')->willReturn(1);
        $this->weeeTax = $this->createMock(Tax::class);
        $this->weeeTax->method('getWeeeAmount')->willReturn('11.26');
        $this->taxData = $this->createPartialMock(
            \Magento\Tax\Helper\Data::class,
            ['getPriceDisplayType', 'priceIncludesTax']
        );

        $this->serializerMock = $this->getMockBuilder(Json::class)
            ->getMock();

        $arguments = [
            'weeeConfig' => $weeeConfig,
            'weeeTax' => $this->weeeTax,
            'taxData' => $this->taxData,
            'serializer' => $this->serializerMock
        ];
        $helper = new ObjectManager($this);
        $this->helperData = $helper->getObject(\Magento\Weee\Helper\Data::class, $arguments);
    }

    public function testGetAmount()
    {
        $this->product->method('hasData')->willReturn(false);
        $this->product->method('getData')->willReturn(11.26);

        $this->assertEquals('11.26', $this->helperData->getAmountExclTax($this->product));
    }

    /**
     * @return Item|MockObject
     */
    private function setupOrderItem()
    {
        $orderItem = $this->getMockBuilder(Item::class)
            ->disableOriginalConstructor()
            ->setMethods(['__wakeup'])
            ->getMock();

        $weeeTaxApplied = [
            [
                WeeeHelper::KEY_WEEE_AMOUNT_INVOICED => self::ROW_AMOUNT_INVOICED,
                WeeeHelper::KEY_BASE_WEEE_AMOUNT_INVOICED => self::BASE_ROW_AMOUNT_INVOICED,
                WeeeHelper::KEY_WEEE_TAX_AMOUNT_INVOICED => self::TAX_AMOUNT_INVOICED,
                WeeeHelper::KEY_BASE_WEEE_TAX_AMOUNT_INVOICED => self::BASE_TAX_AMOUNT_INVOICED,
                WeeeHelper::KEY_WEEE_AMOUNT_REFUNDED => self::ROW_AMOUNT_REFUNDED,
                WeeeHelper::KEY_BASE_WEEE_AMOUNT_REFUNDED => self::BASE_ROW_AMOUNT_REFUNDED,
                WeeeHelper::KEY_WEEE_TAX_AMOUNT_REFUNDED => self::TAX_AMOUNT_REFUNDED,
                WeeeHelper::KEY_BASE_WEEE_TAX_AMOUNT_REFUNDED => self::BASE_TAX_AMOUNT_REFUNDED,
            ],
            [
                WeeeHelper::KEY_WEEE_AMOUNT_INVOICED => self::ROW_AMOUNT_INVOICED,
                WeeeHelper::KEY_BASE_WEEE_AMOUNT_INVOICED => self::BASE_ROW_AMOUNT_INVOICED,
                WeeeHelper::KEY_WEEE_TAX_AMOUNT_INVOICED => self::TAX_AMOUNT_INVOICED,
                WeeeHelper::KEY_BASE_WEEE_TAX_AMOUNT_INVOICED => self::BASE_TAX_AMOUNT_INVOICED,
                WeeeHelper::KEY_WEEE_AMOUNT_REFUNDED => self::ROW_AMOUNT_REFUNDED,
                WeeeHelper::KEY_BASE_WEEE_AMOUNT_REFUNDED => self::BASE_ROW_AMOUNT_REFUNDED,
                WeeeHelper::KEY_WEEE_TAX_AMOUNT_REFUNDED => self::TAX_AMOUNT_REFUNDED,
                WeeeHelper::KEY_BASE_WEEE_TAX_AMOUNT_REFUNDED => self::BASE_TAX_AMOUNT_REFUNDED,
            ],
        ];

        $orderItem->setData(
            'weee_tax_applied',
            json_encode($weeeTaxApplied)
        );

        $this->serializerMock
            ->method('unserialize')
            ->willReturn($weeeTaxApplied);

        return $orderItem;
    }

    public function testGetWeeeAmountInvoiced()
    {
        $orderItem = $this->setupOrderItem();
        $value = $this->helperData->getWeeeAmountInvoiced($orderItem);
        $this->assertEquals(self::ROW_AMOUNT_INVOICED, $value);
    }

    public function testGetBaseWeeeAmountInvoiced()
    {
        $orderItem = $this->setupOrderItem();
        $value = $this->helperData->getBaseWeeeAmountInvoiced($orderItem);
        $this->assertEquals(self::BASE_ROW_AMOUNT_INVOICED, $value);
    }

    public function testGetWeeeTaxAmountInvoiced()
    {
        $orderItem = $this->setupOrderItem();
        $value = $this->helperData->getWeeeTaxAmountInvoiced($orderItem);
        $this->assertEquals(self::TAX_AMOUNT_INVOICED, $value);
    }

    public function testGetWeeeBaseTaxAmountInvoiced()
    {
        $orderItem = $this->setupOrderItem();
        $value = $this->helperData->getBaseWeeeTaxAmountInvoiced($orderItem);
        $this->assertEquals(self::BASE_TAX_AMOUNT_INVOICED, $value);
    }

    public function testGetWeeeAmountRefunded()
    {
        $orderItem = $this->setupOrderItem();
        $value = $this->helperData->getWeeeAmountRefunded($orderItem);
        $this->assertEquals(self::ROW_AMOUNT_REFUNDED, $value);
    }

    public function testGetBaseWeeeAmountRefunded()
    {
        $orderItem = $this->setupOrderItem();
        $value = $this->helperData->getBaseWeeeAmountRefunded($orderItem);
        $this->assertEquals(self::BASE_ROW_AMOUNT_REFUNDED, $value);
    }

    public function testGetWeeeTaxAmountRefunded()
    {
        $orderItem = $this->setupOrderItem();
        $value = $this->helperData->getWeeeTaxAmountRefunded($orderItem);
        $this->assertEquals(self::TAX_AMOUNT_REFUNDED, $value);
    }

    public function testGetBaseWeeeTaxAmountRefunded()
    {
        $orderItem = $this->setupOrderItem();
        $value = $this->helperData->getBaseWeeeTaxAmountRefunded($orderItem);
        $this->assertEquals(self::BASE_TAX_AMOUNT_REFUNDED, $value);
    }

    /**
     * @dataProvider dataProviderGetWeeeAttributesForBundle
     * @param int $priceIncludesTax
     * @param bool $priceDisplay
     * @param array $expectedAmount
     */
    public function testGetWeeeAttributesForBundle($priceDisplay, $priceIncludesTax, $expectedAmount)
    {
        $prodId1 = 1;
        $prodId2 = 2;
        $fptCode1 = 'fpt' . $prodId1;
        $fptCode2 = 'fpt' . $prodId2;

        $weeeObject1 = new DataObject(
            [
                'code' => $fptCode1,
                'amount' => '15.00',
                'amount_excl_tax' => '15.0000',
                'tax_amount' => '1'
            ]
        );
        $weeeObject2 = new DataObject(
            [
                'code' => $fptCode2,
                'amount' => '10.00',
                'amount_excl_tax' => '10.0000',
                'tax_amount' => '5'
            ]
        );
        $expectedObject1 = new DataObject(
            [
                'code' => $fptCode1,
                'amount' => $expectedAmount[0],
                'amount_excl_tax' => '15.0000',
                'tax_amount' => '1'
            ]
        );
        $expectedObject2 = new DataObject(
            [
                'code' => $fptCode2,
                'amount' => $expectedAmount[1],
                'amount_excl_tax' => '10.0000',
                'tax_amount' => '5'
            ]
        );

        $expectedArray = [$prodId1 => [$fptCode1 => $expectedObject1], $prodId2 => [$fptCode2 => $expectedObject2]];
        $this->weeeTax
            ->method('getProductWeeeAttributes')
            ->willReturn([$weeeObject1, $weeeObject2]);
        $this->taxData
            ->method('getPriceDisplayType')
            ->willReturn($priceDisplay);
        $this->taxData
            ->method('priceIncludesTax')
            ->willReturn($priceIncludesTax);

        $productSimple = $this->getMockBuilder(Simple::class)
            ->addMethods(['getId'])
            ->disableOriginalConstructor()
            ->getMock();
        $productSimple->expects($this->at(0))
            ->method('getId')
            ->willReturn($prodId1);
        $productSimple->expects($this->at(1))
            ->method('getId')
            ->willReturn($prodId2);

        $productInstance = $this->createMock(Type::class);
        $productInstance
            ->method('getSelectionsCollection')
            ->willReturn([$productSimple]);

        $store=$this->createMock(Store::class);
        /** @var Product $product */
        $product = $this->createPartialMock(
            Product::class,
            ['getTypeInstance', 'getStoreId', 'getStore', 'getTypeId']
        );
        $product
            ->method('getTypeInstance')
            ->willReturn($productInstance);
        $product
            ->method('getStoreId')
            ->willReturn(1);
        $product
            ->method('getStore')
            ->willReturn($store);
        $product
            ->method('getTypeId')
            ->willReturn('bundle');

        $registry = $this->createMock(Registry::class);
        $registry
            ->method('registry')
            ->with('current_product')
            ->willReturn($product);

        $result = $this->helperData->getWeeeAttributesForBundle($product);
        $this->assertEquals($expectedArray, $result);
    }

    /**
     * @return array
     */
    public function dataProviderGetWeeeAttributesForBundle()
    {
        return [
            [2, false, ["16.00", "15.00"]],
            [2, true, ["15.00", "10.00"]],
            [1, false, ["15.00", "10.00"]],
            [1, true, ["15.0000", "10.0000"]],
            [3, false, ["16.00", "15.00"]],
            [3, true, ["15.00", "10.00"]],
        ];
    }

    public function testGetAppliedSimple()
    {
        $testArray = ['key' => 'value'];
        $itemProductSimple = $this->getMockBuilder(\Magento\Quote\Model\Quote\Item::class)
            ->addMethods(['getWeeeTaxApplied', 'getHasChildren'])
            ->disableOriginalConstructor()
            ->getMock();
        $itemProductSimple
            ->method('getHasChildren')
            ->willReturn(false);

        $itemProductSimple
            ->method('getWeeeTaxApplied')
            ->willReturn(json_encode($testArray));

        $this->serializerMock
            ->method('unserialize')
            ->willReturn($testArray);

        $this->assertEquals($testArray, $this->helperData->getApplied($itemProductSimple));
    }

    public function testGetAppliedBundle()
    {
        $testArray1 = ['key1' => 'value1'];
        $testArray2 = ['key2' => 'value2'];

        $testArray = array_merge($testArray1, $testArray2);

        $itemProductSimple1 = $this->getMockBuilder(\Magento\Quote\Model\Quote\Item::class)
            ->addMethods(['getWeeeTaxApplied'])
            ->disableOriginalConstructor()
            ->getMock();
        $itemProductSimple2 = $this->getMockBuilder(\Magento\Quote\Model\Quote\Item::class)
            ->addMethods(['getWeeeTaxApplied'])
            ->disableOriginalConstructor()
            ->getMock();

        $itemProductSimple1
            ->method('getWeeeTaxApplied')
            ->willReturn(json_encode($testArray1));

        $itemProductSimple2
            ->method('getWeeeTaxApplied')
            ->willReturn(json_encode($testArray2));

        $itemProductBundle = $this->getMockBuilder(\Magento\Quote\Model\Quote\Item::class)
            ->addMethods(['getHasChildren'])
            ->onlyMethods(['isChildrenCalculated', 'getChildren'])
            ->disableOriginalConstructor()
            ->getMock();
        $itemProductBundle
            ->method('getHasChildren')
            ->willReturn(true);
        $itemProductBundle
            ->method('isChildrenCalculated')
            ->willReturn(true);
        $itemProductBundle
            ->method('getChildren')
            ->willReturn([$itemProductSimple1, $itemProductSimple2]);

        $this->serializerMock
            ->method('unserialize')
            ->willReturn($testArray);

        $this->assertEquals($testArray, $this->helperData->getApplied($itemProductBundle));
    }

    public function testGetRecursiveAmountSimple()
    {
        $testAmountUnit = 2;
        $testAmountRow = 34;

        $itemProductSimple = $this->getMockBuilder(\Magento\Quote\Model\Quote\Item::class)
            ->addMethods(['getHasChildren', 'getWeeeTaxAppliedAmount', 'getWeeeTaxAppliedRowAmount'])
            ->disableOriginalConstructor()
            ->getMock();
        $itemProductSimple
            ->method('getHasChildren')
            ->willReturn(false);

        $itemProductSimple
            ->method('getWeeeTaxAppliedAmount')
            ->willReturn($testAmountUnit);
        $itemProductSimple
            ->method('getWeeeTaxAppliedRowAmount')
            ->willReturn($testAmountRow);

        $this->assertEquals($testAmountUnit, $this->helperData->getWeeeTaxAppliedAmount($itemProductSimple));
        $this->assertEquals($testAmountRow, $this->helperData->getWeeeTaxAppliedRowAmount($itemProductSimple));
    }

    public function testGetRecursiveAmountBundle()
    {
        $testAmountUnit1 = 1;
        $testAmountUnit2 = 2;
        $testTotalUnit = $testAmountUnit1 + $testAmountUnit2;

        $testAmountRow1 = 33;
        $testAmountRow2 = 444;
        $testTotalRow = $testAmountRow1 + $testAmountRow2;

        $itemProductSimple1 = $this->getMockBuilder(\Magento\Quote\Model\Quote\Item::class)
            ->addMethods(['getWeeeTaxAppliedAmount', 'getWeeeTaxAppliedRowAmount'])
            ->disableOriginalConstructor()
            ->getMock();
        $itemProductSimple2 = $this->getMockBuilder(\Magento\Quote\Model\Quote\Item::class)
            ->addMethods(['getWeeeTaxAppliedAmount', 'getWeeeTaxAppliedRowAmount'])
            ->disableOriginalConstructor()
            ->getMock();

        $itemProductSimple1
            ->method('getWeeeTaxAppliedAmount')
            ->willReturn($testAmountUnit1);
        $itemProductSimple1
            ->method('getWeeeTaxAppliedRowAmount')
            ->willReturn($testAmountRow1);

        $itemProductSimple2
            ->method('getWeeeTaxAppliedAmount')
            ->willReturn($testAmountUnit2);
        $itemProductSimple2
            ->method('getWeeeTaxAppliedRowAmount')
            ->willReturn($testAmountRow2);

        $itemProductBundle = $this->getMockBuilder(\Magento\Quote\Model\Quote\Item::class)
            ->addMethods(['getHasChildren'])
            ->onlyMethods(['isChildrenCalculated', 'getChildren'])
            ->disableOriginalConstructor()
            ->getMock();
        $itemProductBundle
            ->method('getHasChildren')
            ->willReturn(true);
        $itemProductBundle
            ->method('isChildrenCalculated')
            ->willReturn(true);
        $itemProductBundle
            ->method('getChildren')
            ->willReturn([$itemProductSimple1, $itemProductSimple2]);

        $this->assertEquals($testTotalUnit, $this->helperData->getWeeeTaxAppliedAmount($itemProductBundle));
        $this->assertEquals($testTotalRow, $this->helperData->getWeeeTaxAppliedRowAmount($itemProductBundle));
    }

    public function testGetProductWeeeAttributesForDisplay()
    {
        $store = $this->createMock(Store::class);
        $this->product
            ->method('getStore')
            ->willReturn($store);

        $result = $this->helperData->getProductWeeeAttributesForDisplay($this->product);
        $this->assertNull($result);
    }

    public function testGetTaxDisplayConfig()
    {
        $expected = 1;
        $taxData = $this->createPartialMock(\Magento\Tax\Helper\Data::class, ['getPriceDisplayType']);
        $taxData->method('getPriceDisplayType')->willReturn($expected);
        $arguments = [
            'taxData' => $taxData,
        ];
        $helper = new ObjectManager($this);
        $helperData = $helper->getObject(\Magento\Weee\Helper\Data::class, $arguments);

        $this->assertEquals($expected, $helperData->getTaxDisplayConfig());
    }

    public function testGetTotalAmounts()
    {
        $item1Weee = 5;
        $item2Weee = 7;
        $expected = $item1Weee + $item2Weee;
        $itemProductSimple1 = $this->getMockBuilder(\Magento\Quote\Model\Quote\Item::class)
            ->addMethods(['getWeeeTaxAppliedRowAmount'])
            ->disableOriginalConstructor()
            ->getMock();
        $itemProductSimple2 = $this->getMockBuilder(\Magento\Quote\Model\Quote\Item::class)
            ->addMethods(['getWeeeTaxAppliedRowAmount'])
            ->disableOriginalConstructor()
            ->getMock();
        $items = [$itemProductSimple1, $itemProductSimple2];

        $itemProductSimple1
            ->method('getWeeeTaxAppliedRowAmount')
            ->willReturn($item1Weee);
        $itemProductSimple2
            ->method('getWeeeTaxAppliedRowAmount')
            ->willReturn($item2Weee);

        $this->assertEquals($expected, $this->helperData->getTotalAmounts($items));
    }

    public function testGetBaseTotalAmounts()
    {
        $item1BaseWeee = 4;
        $item2BaseWeee = 3;
        $expected = $item1BaseWeee + $item2BaseWeee;
        $itemProductSimple1 = $this->getMockBuilder(\Magento\Quote\Model\Quote\Item::class)
            ->addMethods(['getBaseWeeeTaxAppliedRowAmnt'])
            ->disableOriginalConstructor()
            ->getMock();
        $itemProductSimple2 = $this->getMockBuilder(\Magento\Quote\Model\Quote\Item::class)
            ->addMethods(['getBaseWeeeTaxAppliedRowAmnt'])
            ->disableOriginalConstructor()
            ->getMock();
        $items = [$itemProductSimple1, $itemProductSimple2];

        $itemProductSimple1
            ->method('getBaseWeeeTaxAppliedRowAmnt')
            ->willReturn($item1BaseWeee);
        $itemProductSimple2
            ->method('getBaseWeeeTaxAppliedRowAmnt')
            ->willReturn($item2BaseWeee);

        $this->assertEquals($expected, $this->helperData->getBaseTotalAmounts($items));
    }
}
