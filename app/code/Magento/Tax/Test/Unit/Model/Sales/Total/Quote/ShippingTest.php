<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Test\Unit\Model\Sales\Total\Quote;

use \Magento\Tax\Model\Sales\Total\Quote\Shipping;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ShippingTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $taxConfigMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $taxCalculationMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $quoteDetailsDataObjectFactory;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $itemDetailsDataObjectFactory;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $taxClassKeyDataObjectFactory;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $addressFactoryMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $regionFactoryMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $quoteMock;

    /**
     * @var Shipping
     */
    private $model;

    protected function setUp(): void
    {
        $this->taxConfigMock = $this->createMock(\Magento\Tax\Model\Config::class);
        $this->taxCalculationMock = $this->createMock(\Magento\Tax\Api\TaxCalculationInterface::class);
        $this->quoteDetailsDataObjectFactory = $this->createPartialMock(
            \Magento\Tax\Api\Data\QuoteDetailsInterfaceFactory::class,
            ['create']
        );
        $this->itemDetailsDataObjectFactory = $this->createPartialMock(
            \Magento\Tax\Api\Data\QuoteDetailsItemInterfaceFactory::class,
            ['create']
        );
        $this->taxClassKeyDataObjectFactory = $this->createPartialMock(
            \Magento\Tax\Api\Data\TaxClassKeyInterfaceFactory::class,
            ['create']
        );
        $this->addressFactoryMock = $this->createMock(\Magento\Customer\Api\Data\AddressInterfaceFactory::class);
        $this->regionFactoryMock = $this->createMock(\Magento\Customer\Api\Data\RegionInterfaceFactory::class);
        $this->quoteMock = $this->createMock(\Magento\Quote\Model\Quote::class);
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

        $shippingMock = $this->createMock(\Magento\Quote\Api\Data\ShippingInterface::class);
        $shippingMock->expects($this->atLeastOnce())->method('getAddress')->willReturn($addressMock);
        $shippingAssignmentMock = $this->createMock(\Magento\Quote\Api\Data\ShippingAssignmentInterface::class);
        $shippingAssignmentMock->expects($this->atLeastOnce())->method('getShipping')->willReturn($shippingMock);
        $shippingAssignmentMock->expects($this->once())
            ->method('getItems')
            ->willReturn([$this->createMock(\Magento\Quote\Api\Data\CartItemInterface::class)]);

        $totalMock = $this->createMock(\Magento\Quote\Model\Quote\Address\Total::class);

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
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    private function getMockObject($className, array $objectState)
    {
        $getterValueMap = [];
        $methods = ['__wakeup'];
        foreach ($objectState as $key => $value) {
            $getterName = 'get' . str_replace('_', '', ucwords($key, '_'));
            $getterValueMap[$getterName] = $value;
            $methods[] = $getterName;
        }

        $mock = $this->createPartialMock($className, $methods);
        foreach ($getterValueMap as $getterName => $value) {
            $mock->expects($this->any())->method($getterName)->willReturn($value);
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
