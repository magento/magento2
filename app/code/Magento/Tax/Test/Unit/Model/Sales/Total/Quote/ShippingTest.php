<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Tax\Test\Unit\Model\Sales\Total\Quote;

use \Magento\Tax\Model\Sales\Total\Quote\Shipping;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ShippingTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $taxConfigMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $taxCalculationMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteDetailsDataObjectFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $itemDetailsDataObjectFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $taxClassKeyDataObjectFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $addressFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $regionFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteMock;

    /**
     * @var Shipping
     */
    private $model;

    protected function setUp()
    {
        $this->taxConfigMock = $this->getMock(\Magento\Tax\Model\Config::class, [], [], '', false);
        $this->taxCalculationMock = $this->getMock(\Magento\Tax\Api\TaxCalculationInterface::class);
        $this->quoteDetailsDataObjectFactory = $this->getMock(
            \Magento\Tax\Api\Data\QuoteDetailsInterfaceFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $this->itemDetailsDataObjectFactory = $this->getMock(
            \Magento\Tax\Api\Data\QuoteDetailsItemInterfaceFactory::class,
            [
                'create',
            ],
            [],
            '',
            false
        );
        $this->taxClassKeyDataObjectFactory = $this->getMock(
            \Magento\Tax\Api\Data\TaxClassKeyInterfaceFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $this->addressFactoryMock = $this->getMock(
            \Magento\Customer\Api\Data\AddressInterfaceFactory::class,
            [],
            [],
            '',
            false
        );
        $this->regionFactoryMock = $this->getMock(
            \Magento\Customer\Api\Data\RegionInterfaceFactory::class,
            [],
            [],
            '',
            false
        );
        $this->quoteMock = $this->getMock(\Magento\Quote\Model\Quote::class, [], [], '', false);
        $this->model = new Shipping(
            $this->taxConfigMock,
            $this->taxCalculationMock,
            $this->quoteDetailsDataObjectFactory,
            $this->itemDetailsDataObjectFactory,
            $this->taxClassKeyDataObjectFactory,
            $this->addressFactoryMock,
            $this->regionFactoryMock
        );
    }

    public function testCollectDoesNotCalculateTaxIfThereIsNoItemsRelatedToGivenAddress()
    {
        $storeId = 1;
        $this->quoteMock->expects($this->once())->method('getStoreId')->willReturn($storeId);

        $addressMock = $this->getMockObject(\Magento\Quote\Model\Quote\Address::class, [
            'all_items' => [],
            'shipping_tax_calculation_amount' => 100,
            'base_shipping_tax_calculation_amount' => 200,
            'shipping_discount_amount' => 10,
            'base_shipping_discount_amount' => 20,
            'quote' => $this->quoteMock,
        ]);
        $this->taxCalculationMock->expects($this->never())->method('calculateTax');

        $shippingMock = $this->getMock(\Magento\Quote\Api\Data\ShippingInterface::class);
        $shippingMock->expects($this->atLeastOnce())->method('getAddress')->willReturn($addressMock);
        $shippingAssignmentMock = $this->getMock(\Magento\Quote\Api\Data\ShippingAssignmentInterface::class);
        $shippingAssignmentMock->expects($this->atLeastOnce())->method('getShipping')->willReturn($shippingMock);
        $shippingAssignmentMock->expects($this->once())
            ->method('getItems')
            ->willReturn([$this->getMock(\Magento\Quote\Api\Data\CartItemInterface::class)]);

        $totalMock = $this->getMock(\Magento\Quote\Model\Quote\Address\Total::class, [], [], '', false);

        $this->model->collect($this->quoteMock, $shippingAssignmentMock, $totalMock);
    }

    public function testCollect()
    {
        $this->markTestIncomplete('Target code is not unit testable. Refactoring is required.');
    }

    /**
     * Retrieve mock object with mocked getters
     *
     * @param $className
     * @param array $objectState
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getMockObject($className, array $objectState)
    {
        $getterValueMap = [];
        $methods = ['__wakeup'];
        foreach ($objectState as $key => $value) {
            $getterName = 'get' . str_replace(' ', '', ucwords(str_replace('_', ' ', $key)));
            $getterValueMap[$getterName] = $value;
            $methods[] = $getterName;
        }

        $mock = $this->getMock($className, $methods, [], '', false);
        foreach ($getterValueMap as $getterName => $value) {
            $mock->expects($this->any())->method($getterName)->will($this->returnValue($value));
        }

        return $mock;
    }

    public function testFetch()
    {
        $value = 42;
        $total = new \Magento\Quote\Model\Quote\Address\Total();
        $total->setShippingInclTax($value);
        $expectedResult = [
            'code' => 'shipping',
            'shipping_incl_tax' => $value
        ];

        $this->assertEquals($expectedResult, $this->model->fetch($this->quoteMock, $total));
    }

    public function testFetchWithZeroShipping()
    {
        $value = 0;
        $total = new \Magento\Quote\Model\Quote\Address\Total();
        $total->setShippingInclTax($value);

        $this->assertNull($this->model->fetch($this->quoteMock, $total));
    }
}
