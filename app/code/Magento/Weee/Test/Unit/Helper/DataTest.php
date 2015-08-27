<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Weee\Test\Unit\Helper;

use Magento\Weee\Helper\Data as WeeeHelper;

class DataTest extends \PHPUnit_Framework_TestCase
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
    protected $_product;

    /**
     * @var \Magento\Weee\Model\Tax
     */
    protected $weeeTax;

    /**
     * @var \Magento\Weee\Helper\Data
     */
    protected $_helperData;

    protected function setUp()
    {
        $this->_product = $this->getMock('Magento\Catalog\Model\Product', [], [], '', false);
        $weeeConfig = $this->getMock('Magento\Weee\Model\Config', [], [], '', false);
        $weeeConfig->expects($this->any())->method('isEnabled')->will($this->returnValue(true));
        $this->weeeTax = $this->getMock('Magento\Weee\Model\Tax', [], [], '', false);
        $this->weeeTax->expects($this->any())->method('getWeeeAmount')->will($this->returnValue('11.26'));
        $arguments = [
            'weeeConfig' => $weeeConfig,
            'weeeTax' => $this->weeeTax,
        ];
        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->_helperData = $helper->getObject('Magento\Weee\Helper\Data', $arguments);
    }

    public function testGetAmount()
    {
        $this->_product->expects($this->any())->method('hasData')->will($this->returnValue(false));
        $this->_product->expects($this->any())->method('getData')->will($this->returnValue(11.26));

        $this->assertEquals('11.26', $this->_helperData->getAmount($this->_product));
    }

    /**
     * @return \Magento\Sales\Model\Order\Item|\PHPUnit_Framework_MockObject_MockObject
     */
    private function setupOrderItem()
    {
        $orderItem = $this->getMockBuilder('\Magento\Sales\Model\Order\Item')
            ->disableOriginalConstructor()
            ->setMethods(['__wakeup'])
            ->getMock();

        $orderItem->setData(
            'weee_tax_applied',
            \Zend_Json::encode(
                [
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
                ]
            )
        );
        return $orderItem;
    }

    public function testGetWeeeAmountInvoiced()
    {
        $orderItem = $this->setupOrderItem();
        $value = $this->_helperData->getWeeeAmountInvoiced($orderItem);
        $this->assertEquals(self::ROW_AMOUNT_INVOICED, $value);
    }

    public function testGetBaseWeeeAmountInvoiced()
    {
        $orderItem = $this->setupOrderItem();
        $value = $this->_helperData->getBaseWeeeAmountInvoiced($orderItem);
        $this->assertEquals(self::BASE_ROW_AMOUNT_INVOICED, $value);
    }

    public function testGetWeeeTaxAmountInvoiced()
    {
        $orderItem = $this->setupOrderItem();
        $value = $this->_helperData->getWeeeTaxAmountInvoiced($orderItem);
        $this->assertEquals(self::TAX_AMOUNT_INVOICED, $value);
    }

    public function testGetWeeeBaseTaxAmountInvoiced()
    {
        $orderItem = $this->setupOrderItem();
        $value = $this->_helperData->getBaseWeeeTaxAmountInvoiced($orderItem);
        $this->assertEquals(self::BASE_TAX_AMOUNT_INVOICED, $value);
    }

    public function testGetWeeeAmountRefunded()
    {
        $orderItem = $this->setupOrderItem();
        $value = $this->_helperData->getWeeeAmountRefunded($orderItem);
        $this->assertEquals(self::ROW_AMOUNT_REFUNDED, $value);
    }

    public function testGetBaseWeeeAmountRefunded()
    {
        $orderItem = $this->setupOrderItem();
        $value = $this->_helperData->getBaseWeeeAmountRefunded($orderItem);
        $this->assertEquals(self::BASE_ROW_AMOUNT_REFUNDED, $value);
    }

    public function testGetWeeeTaxAmountRefunded()
    {
        $orderItem = $this->setupOrderItem();
        $value = $this->_helperData->getWeeeTaxAmountRefunded($orderItem);
        $this->assertEquals(self::TAX_AMOUNT_REFUNDED, $value);
    }

    public function testGetBaseWeeeTaxAmountRefunded()
    {
        $orderItem = $this->setupOrderItem();
        $value = $this->_helperData->getBaseWeeeTaxAmountRefunded($orderItem);
        $this->assertEquals(self::BASE_TAX_AMOUNT_REFUNDED, $value);
    }

    public function testGetWeeAttributesForBundle()
    {

        $prodId1 = rand(1, 10);
        $prodId2 = rand(11, 20);
        $fptCode1 = 'fpt'.$prodId1;
        $fptCode2 = 'fpt'.$prodId2;

        $weeObject1 = new \Magento\Framework\DataObject(
            [
                'code' => $fptCode1,
                'amount' => '15.0000',
            ]
        );

        $weeObject2 = new \Magento\Framework\DataObject(
            [
                'code' => $fptCode2,
                'amount' => '15.0000',
            ]
        );


        $testArray = [$prodId1 => [$fptCode1 => $weeObject1], $prodId2 => [$fptCode2 => $weeObject2]];

        $this->weeeTax->expects($this->any())
            ->method('getProductWeeeAttributes')
            ->will($this->returnValue([$weeObject1, $weeObject2]));

        $productSimple=$this->getMock('\Magento\Catalog\Model\Product\Type\Simple', ['getId'], [], '', false);

        $productSimple->expects($this->at(0))
            ->method('getId')
            ->will($this->returnValue($prodId1));
        $productSimple->expects($this->at(1))
            ->method('getId')
            ->will($this->returnValue($prodId2));

        $productInstance=$this->getMock('\Magento\Bundle\Model\Product\Type', [], [], '', false);
        $productInstance->expects($this->any())
            ->method('getSelectionsCollection')
            ->will($this->returnValue([$productSimple]));

        $store=$this->getMock('\Magento\Store\Model\Store', [], [], '', false);


        $product=$this->getMock(
            '\Magento\Bundle\Model\Product',
            ['getTypeInstance', 'getStoreId', 'getStore', 'getTypeId'],
            [],
            '',
            false
        );
        $product->expects($this->any())
            ->method('getTypeInstance')
            ->will($this->returnValue($productInstance));

        $product->expects($this->any())
            ->method('getStoreId')
            ->will($this->returnValue(1));

        $product->expects($this->any())
            ->method('getStore')
            ->will($this->returnValue($store));

        $product->expects($this->any())
            ->method('getTypeId')
            ->will($this->returnValue('bundle'));

        $registry=$this->getMock('Magento\Framework\Registry', [], [], '', false);
        $registry->expects($this->any())
            ->method('registry')
            ->with('current_product')
            ->will($this->returnValue($product));

        $this->assertEquals($testArray, $this->_helperData->getWeeAttributesForBundle($product));
    }
}
