<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Test\Unit\Model\Calculation;

use \Magento\Tax\Model\Calculation\CalculatorFactory;

use Magento\Customer\Api\Data\AddressInterface as CustomerAddress;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test class for \Magento\Tax\Model\CalculatorFactory
 */
class CalculatorFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    public $objectManager;

    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);
    }

    /**
     * @param string $type Type of calculator
     * @param int $storeId
     * @param CustomerAddress $billingAddress
     * @param CustomerAddress $shippingAddress
     * @param null|int $customerTaxClassId
     * @param null|int $customerId
     * @param \Magento\Tax\Model\Calculation\AbstractCalculator $expectedInstanceType
     *  expected type of calculator instance
     *
     * @dataProvider createDataProvider
     */
    public function testCreate(
        $type,
        $storeId,
        $billingAddress,
        $shippingAddress,
        $customerTaxClassId,
        $customerId,
        $expectedInstanceType
    ) {
        $instanceMock = $this->getMockBuilder($expectedInstanceType)->disableOriginalConstructor()->getMock();
        $objectManagerMock = $this->getMock(\Magento\Framework\ObjectManagerInterface::class);

        // Verify create() is called with correct concrete type
        $objectManagerMock->expects($this->once())
            ->method('create')
            ->with($expectedInstanceType, ['storeId' => $storeId])
            ->will($this->returnValue($instanceMock));

        /** @var CalculatorFactory $calculatorFactory */
        $calculatorFactory = $this->objectManager->getObject(
            \Magento\Tax\Model\Calculation\CalculatorFactory::class,
            ['objectManager' => $objectManagerMock]
        );

        // Verify billing is set correctly if passed in
        if ($billingAddress != null) {
            $instanceMock->expects($this->once())
                ->method('setBillingAddress')
                ->with($billingAddress);
        } else {
            $instanceMock->expects($this->never())
                ->method('setBillingAddress');
        }

        // Verify shipping is set correctly if passed in
        if ($shippingAddress != null) {
            $instanceMock->expects($this->once())
                ->method('setShippingAddress')
                ->with($shippingAddress);
        } else {
            $instanceMock->expects($this->never())
                ->method('setShippingAddress');
        }

        // Verify customerTaxClassId is set correctly if passed in
        if ($customerTaxClassId != null) {
            $instanceMock->expects($this->once())
                ->method('setCustomerTaxClassId')
                ->with($customerTaxClassId);
        } else {
            $instanceMock->expects($this->never())
                ->method('setCustomerTaxClassId');
        }

        // Verify customerId is set correctly if passed in
        if ($customerId != null) {
            $instanceMock->expects($this->once())
                ->method('setCustomerId')
                ->with($customerId);
        } else {
            $instanceMock->expects($this->never())
                ->method('setCustomerId');
        }

        // Call create()
        $calculator = $calculatorFactory
            ->create($type, $storeId, $billingAddress, $shippingAddress, $customerTaxClassId, $customerId);

        // Verify correct type is returned
        $this->assertInstanceOf($expectedInstanceType, $calculator);
    }

    /**
     * Returns a set of 'true' and 'false' parameters for each of the setter/getter method pairs
     *
     * @return array
     */
    public function createDataProvider()
    {
        $billingAddressMock = $this->getMockBuilder(\Magento\Customer\Api\Data\AddressInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $shippingAddressMock = $this->getMockBuilder(\Magento\Customer\Api\Data\AddressInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        return [
            'Unit' => [
                CalculatorFactory::CALC_UNIT_BASE,
                1,
                null,
                null,
                null,
                null, \Magento\Tax\Model\Calculation\UnitBaseCalculator::class,
            ],
            'Row HasBilling' => [
                CalculatorFactory::CALC_ROW_BASE,
                2,
                $billingAddressMock,
                null,
                null,
                null, \Magento\Tax\Model\Calculation\RowBaseCalculator::class,
            ],
            'Row HasCustomerTaxClassId' => [
                CalculatorFactory::CALC_ROW_BASE,
                3,
                null,
                null,
                123,
                null, \Magento\Tax\Model\Calculation\RowBaseCalculator::class,
            ],
            'Total HasShipping' => [
                CalculatorFactory::CALC_TOTAL_BASE,
                1,
                null,
                $shippingAddressMock,
                null,
                null, \Magento\Tax\Model\Calculation\TotalBaseCalculator::class,
            ],
            'Total HasShipping HasBilling HasCustomerTaxClassId' => [
                CalculatorFactory::CALC_TOTAL_BASE,
                1,
                $billingAddressMock,
                $shippingAddressMock,
                1,
                null, \Magento\Tax\Model\Calculation\TotalBaseCalculator::class,
            ],
            'Total HasShipping HasBilling HasCustomerTaxClassId, HasCustomer' => [
                CalculatorFactory::CALC_TOTAL_BASE,
                1,
                $billingAddressMock,
                $shippingAddressMock,
                1,
                1, \Magento\Tax\Model\Calculation\TotalBaseCalculator::class,
            ],
        ];
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Unknown calculation type: NOT_A_TYPE
     */
    public function testCreateInvalid()
    {
        /** @var CalculatorFactory $calculatorFactory */
        $calculatorFactory = $this->objectManager->getObject(
            \Magento\Tax\Model\Calculation\CalculatorFactory::class
        );

        // Call create() with a bad type to generate exception
        $calculatorFactory->create('NOT_A_TYPE', 1);
    }
}
