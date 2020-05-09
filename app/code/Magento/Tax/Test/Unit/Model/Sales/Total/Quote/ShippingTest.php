<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Tax\Test\Unit\Model\Sales\Total\Quote;

use Magento\Customer\Api\Data\AddressInterfaceFactory;
use Magento\Customer\Api\Data\RegionInterfaceFactory;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Api\Data\ShippingInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\Address\Total;
use Magento\Tax\Api\Data\QuoteDetailsInterfaceFactory;
use Magento\Tax\Api\Data\QuoteDetailsItemInterfaceFactory;
use Magento\Tax\Api\Data\TaxClassKeyInterfaceFactory;
use Magento\Tax\Api\TaxCalculationInterface;
use Magento\Tax\Model\Config;
use Magento\Tax\Model\Sales\Total\Quote\Shipping;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ShippingTest extends TestCase
{
    /**
     * @var MockObject
     */
    private $taxConfigMock;

    /**
     * @var MockObject
     */
    private $taxCalculationMock;

    /**
     * @var MockObject
     */
    private $quoteDetailsDataObjectFactory;

    /**
     * @var MockObject
     */
    private $itemDetailsDataObjectFactory;

    /**
     * @var MockObject
     */
    private $taxClassKeyDataObjectFactory;

    /**
     * @var MockObject
     */
    private $addressFactoryMock;

    /**
     * @var MockObject
     */
    private $regionFactoryMock;

    /**
     * @var MockObject
     */
    private $quoteMock;

    /**
     * @var Shipping
     */
    private $model;

    protected function setUp(): void
    {
        $this->taxConfigMock = $this->createMock(Config::class);
        $this->taxCalculationMock = $this->getMockForAbstractClass(TaxCalculationInterface::class);
        $this->quoteDetailsDataObjectFactory = $this->createPartialMock(
            QuoteDetailsInterfaceFactory::class,
            ['create']
        );
        $this->itemDetailsDataObjectFactory = $this->createPartialMock(
            QuoteDetailsItemInterfaceFactory::class,
            ['create']
        );
        $this->taxClassKeyDataObjectFactory = $this->createPartialMock(
            TaxClassKeyInterfaceFactory::class,
            ['create']
        );
        $this->addressFactoryMock = $this->createMock(AddressInterfaceFactory::class);
        $this->regionFactoryMock = $this->createMock(RegionInterfaceFactory::class);
        $this->quoteMock = $this->createMock(Quote::class);
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

        $addressMock = $this->getMockObject(Address::class, [
            'all_items' => [],
            'shipping_tax_calculation_amount' => 100,
            'base_shipping_tax_calculation_amount' => 200,
            'shipping_discount_amount' => 10,
            'base_shipping_discount_amount' => 20,
            'quote' => $this->quoteMock,
        ]);
        $this->taxCalculationMock->expects($this->never())->method('calculateTax');

        $shippingMock = $this->getMockForAbstractClass(ShippingInterface::class);
        $shippingMock->expects($this->atLeastOnce())->method('getAddress')->willReturn($addressMock);
        $shippingAssignmentMock = $this->getMockForAbstractClass(ShippingAssignmentInterface::class);
        $shippingAssignmentMock->expects($this->atLeastOnce())->method('getShipping')->willReturn($shippingMock);
        $shippingAssignmentMock->expects($this->once())
            ->method('getItems')
            ->willReturn([$this->getMockForAbstractClass(CartItemInterface::class)]);

        $totalMock = $this->createMock(Total::class);

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
     * @return MockObject
     */
    private function getMockObject($className, array $objectState)
    {
        $getterValueMap = [];
        $methods = [];
        foreach ($objectState as $key => $value) {
            $getterName = 'get' . str_replace('_', '', ucwords($key, '_'));
            $getterValueMap[$getterName] = $value;
            $methods[] = $getterName;
        }

        $mock = $this->getMockBuilder($className)
            ->disableOriginalConstructor()
            ->addMethods(array_diff($methods, get_class_methods($className)))
            ->onlyMethods(get_class_methods($className))
            ->getMock();
        foreach ($getterValueMap as $getterName => $value) {
            $mock->expects($this->any())->method($getterName)->willReturn($value);
        }

        return $mock;
    }

    public function testFetch()
    {
        $value = 42;
        $total = new Total();
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
        $total = new Total();
        $total->setShippingInclTax($value);

        $this->assertNull($this->model->fetch($this->quoteMock, $total));
    }
}
