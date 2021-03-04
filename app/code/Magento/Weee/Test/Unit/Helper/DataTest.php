<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Weee\Test\Unit\Helper;

use Magento\Catalog\Model\Product\Type\Simple;
use Magento\Weee\Helper\Data as WeeeHelper;

/**
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DataTest extends \PHPUnit\Framework\TestCase
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
     * @var \Magento\Catalog\Model\Product
     */
    protected $product;

    /**
     * @var \Magento\Weee\Model\Tax
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

    /** @var \Magento\Framework\Serialize\Serializer\Json|\PHPUnit\Framework\MockObject\MockObject */
    private $serializerMock;

    protected function setUp(): void
    {
        $this->product = $this->createMock(\Magento\Catalog\Model\Product::class);
        $weeeConfig = $this->createMock(\Magento\Weee\Model\Config::class);
        $weeeConfig->expects($this->any())->method('isEnabled')->willReturn(true);
        $weeeConfig->expects($this->any())->method('getListPriceDisplayType')->willReturn(1);
        $this->weeeTax = $this->createMock(\Magento\Weee\Model\Tax::class);
        $this->weeeTax->expects($this->any())->method('getWeeeAmount')->willReturn('11.26');
        $this->taxData = $this->createPartialMock(
            \Magento\Tax\Helper\Data::class,
            ['getPriceDisplayType', 'priceIncludesTax']
        );

        $this->serializerMock = $this->getMockBuilder(\Magento\Framework\Serialize\Serializer\Json::class)->getMock();

        $arguments = [
            'weeeConfig' => $weeeConfig,
            'weeeTax' => $this->weeeTax,
            'taxData' => $this->taxData,
            'serializer'  => $this->serializerMock
        ];
        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->helperData = $helper->getObject(\Magento\Weee\Helper\Data::class, $arguments);
    }

    public function testGetAmount()
    {
        $this->product->expects($this->any())->method('hasData')->willReturn(false);
        $this->product->expects($this->any())->method('getData')->willReturn(11.26);

        $this->assertEquals('11.26', $this->helperData->getAmountExclTax($this->product));
    }

    /**
     * @return \Magento\Sales\Model\Order\Item|\PHPUnit\Framework\MockObject\MockObject
     */
    private function setupOrderItem()
    {
        $orderItem = $this->getMockBuilder(\Magento\Sales\Model\Order\Item::class)
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

        $this->serializerMock->expects($this->any())
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

        $weeeObject1 = new \Magento\Framework\DataObject(
            [
                'code' => $fptCode1,
                'amount' => '15.00',
                'amount_excl_tax' => '15.0000',
                'tax_amount' => '1'
            ]
        );
        $weeeObject2 = new \Magento\Framework\DataObject(
            [
                'code' => $fptCode2,
                'amount' => '10.00',
                'amount_excl_tax' => '10.0000',
                'tax_amount' => '5'
            ]
        );
        $expectedObject1 = new \Magento\Framework\DataObject(
            [
                'code' => $fptCode1,
                'amount' => $expectedAmount[0],
                'amount_excl_tax' => '15.0000',
                'tax_amount' => '1'
            ]
        );
        $expectedObject2 = new \Magento\Framework\DataObject(
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

        $productInstance = $this->createMock(\Magento\Bundle\Model\Product\Type::class);
        $productInstance
            ->method('getSelectionsCollection')
            ->willReturn([$productSimple]);

        $store=$this->createMock(\Magento\Store\Model\Store::class);
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $this->createPartialMock(
            \Magento\Catalog\Model\Product::class,
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

        $registry=$this->createMock(\Magento\Framework\Registry::class);
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
        $itemProductSimple = $this->createPartialMock(
            \Magento\Quote\Model\Quote\Item::class,
            ['getWeeeTaxApplied', 'getHasChildren']
        );
        $itemProductSimple->expects($this->any())
            ->method('getHasChildren')
            ->willReturn(false);

        $itemProductSimple->expects($this->any())
            ->method('getWeeeTaxApplied')
            ->willReturn(json_encode($testArray));

        $this->serializerMock->expects($this->any())
            ->method('unserialize')
            ->willReturn($testArray);

        $this->assertEquals($testArray, $this->helperData->getApplied($itemProductSimple));
    }

    public function testGetAppliedBundle()
    {
        $testArray1 = ['key1' => 'value1'];
        $testArray2 = ['key2' => 'value2'];

        $testArray = array_merge($testArray1, $testArray2);

        $itemProductSimple1=$this->createPartialMock(\Magento\Quote\Model\Quote\Item::class, ['getWeeeTaxApplied']);
        $itemProductSimple2=$this->createPartialMock(\Magento\Quote\Model\Quote\Item::class, ['getWeeeTaxApplied']);

        $itemProductSimple1->expects($this->any())
            ->method('getWeeeTaxApplied')
            ->willReturn(json_encode($testArray1));

        $itemProductSimple2->expects($this->any())
            ->method('getWeeeTaxApplied')
            ->willReturn(json_encode($testArray2));

        $itemProductBundle = $this->createPartialMock(
            \Magento\Quote\Model\Quote\Item::class,
            ['getHasChildren', 'isChildrenCalculated', 'getChildren']
        );
        $itemProductBundle->expects($this->any())
            ->method('getHasChildren')
            ->willReturn(true);
        $itemProductBundle->expects($this->any())
            ->method('isChildrenCalculated')
            ->willReturn(true);
        $itemProductBundle->expects($this->any())
            ->method('getChildren')
            ->willReturn([$itemProductSimple1, $itemProductSimple2]);

        $this->serializerMock->expects($this->any())
            ->method('unserialize')
            ->willReturn($testArray);

        $this->assertEquals($testArray, $this->helperData->getApplied($itemProductBundle));
    }

    public function testGetRecursiveAmountSimple()
    {
        $testAmountUnit = 2;
        $testAmountRow = 34;

        $itemProductSimple = $this->createPartialMock(
            \Magento\Quote\Model\Quote\Item::class,
            ['getHasChildren', 'getWeeeTaxAppliedAmount', 'getWeeeTaxAppliedRowAmount']
        );
        $itemProductSimple->expects($this->any())
            ->method('getHasChildren')
            ->willReturn(false);

        $itemProductSimple->expects($this->any())
            ->method('getWeeeTaxAppliedAmount')
            ->willReturn($testAmountUnit);
        $itemProductSimple->expects($this->any())
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

        $itemProductSimple1 = $this->createPartialMock(
            \Magento\Quote\Model\Quote\Item::class,
            ['getWeeeTaxAppliedAmount', 'getWeeeTaxAppliedRowAmount']
        );
        $itemProductSimple2 = $this->createPartialMock(
            \Magento\Quote\Model\Quote\Item::class,
            ['getWeeeTaxAppliedAmount', 'getWeeeTaxAppliedRowAmount']
        );

        $itemProductSimple1->expects($this->any())
            ->method('getWeeeTaxAppliedAmount')
            ->willReturn($testAmountUnit1);
        $itemProductSimple1->expects($this->any())
            ->method('getWeeeTaxAppliedRowAmount')
            ->willReturn($testAmountRow1);

        $itemProductSimple2->expects($this->any())
            ->method('getWeeeTaxAppliedAmount')
            ->willReturn($testAmountUnit2);
        $itemProductSimple2->expects($this->any())
            ->method('getWeeeTaxAppliedRowAmount')
            ->willReturn($testAmountRow2);

        $itemProductBundle = $this->createPartialMock(
            \Magento\Quote\Model\Quote\Item::class,
            ['getHasChildren', 'isChildrenCalculated', 'getChildren']
        );
        $itemProductBundle->expects($this->any())
            ->method('getHasChildren')
            ->willReturn(true);
        $itemProductBundle->expects($this->any())
            ->method('isChildrenCalculated')
            ->willReturn(true);
        $itemProductBundle->expects($this->any())
            ->method('getChildren')
            ->willReturn([$itemProductSimple1, $itemProductSimple2]);

        $this->assertEquals($testTotalUnit, $this->helperData->getWeeeTaxAppliedAmount($itemProductBundle));
        $this->assertEquals($testTotalRow, $this->helperData->getWeeeTaxAppliedRowAmount($itemProductBundle));
    }

    public function testGetProductWeeeAttributesForDisplay()
    {
        $store = $this->createMock(\Magento\Store\Model\Store::class);
        $this->product->expects($this->any())
            ->method('getStore')
            ->willReturn($store);

        $result = $this->helperData->getProductWeeeAttributesForDisplay($this->product);
        $this->assertNull($result);
    }

    public function testGetTaxDisplayConfig()
    {
        $expected = 1;
        $taxData = $this->createPartialMock(\Magento\Tax\Helper\Data::class, ['getPriceDisplayType']);
        $taxData->expects($this->any())->method('getPriceDisplayType')->willReturn($expected);
        $arguments = [
            'taxData' => $taxData,
        ];
        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $helperData = $helper->getObject(\Magento\Weee\Helper\Data::class, $arguments);

        $this->assertEquals($expected, $helperData->getTaxDisplayConfig());
    }

    public function testGetTotalAmounts()
    {
        $item1Weee = 5;
        $item2Weee = 7;
        $expected = $item1Weee + $item2Weee;
        $itemProductSimple1 = $this->createPartialMock(
            \Magento\Quote\Model\Quote\Item::class,
            ['getWeeeTaxAppliedRowAmount']
        );
        $itemProductSimple2 = $this->createPartialMock(
            \Magento\Quote\Model\Quote\Item::class,
            ['getWeeeTaxAppliedRowAmount']
        );
        $items = [$itemProductSimple1, $itemProductSimple2];

        $itemProductSimple1->expects($this->any())
            ->method('getWeeeTaxAppliedRowAmount')
            ->willReturn($item1Weee);
        $itemProductSimple2->expects($this->any())
            ->method('getWeeeTaxAppliedRowAmount')
            ->willReturn($item2Weee);

        $this->assertEquals($expected, $this->helperData->getTotalAmounts($items));
    }

    public function testGetBaseTotalAmounts()
    {
        $item1BaseWeee = 4;
        $item2BaseWeee = 3;
        $expected = $item1BaseWeee + $item2BaseWeee;
        $itemProductSimple1 = $this->createPartialMock(
            \Magento\Quote\Model\Quote\Item::class,
            ['getBaseWeeeTaxAppliedRowAmnt']
        );
        $itemProductSimple2 = $this->createPartialMock(
            \Magento\Quote\Model\Quote\Item::class,
            ['getBaseWeeeTaxAppliedRowAmnt']
        );
        $items = [$itemProductSimple1, $itemProductSimple2];

        $itemProductSimple1->expects($this->any())
            ->method('getBaseWeeeTaxAppliedRowAmnt')
            ->willReturn($item1BaseWeee);
        $itemProductSimple2->expects($this->any())
            ->method('getBaseWeeeTaxAppliedRowAmnt')
            ->willReturn($item2BaseWeee);

        $this->assertEquals($expected, $this->helperData->getBaseTotalAmounts($items));
    }
}
