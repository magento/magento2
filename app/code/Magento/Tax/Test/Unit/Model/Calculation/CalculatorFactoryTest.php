<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Tax\Test\Unit\Model\Calculation;

use Magento\Customer\Api\Data\AddressInterface as CustomerAddress;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Tax\Model\Calculation\AbstractCalculator;
use Magento\Tax\Model\Calculation\CalculatorFactory;
use Magento\Tax\Model\Calculation\RowBaseCalculator;
use Magento\Tax\Model\Calculation\TotalBaseCalculator;

use Magento\Tax\Model\Calculation\UnitBaseCalculator;
use PHPUnit\Framework\TestCase;

/**
 * Test class for \Magento\Tax\Model\CalculatorFactory
 */
class CalculatorFactoryTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    public $objectManager;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
    }

    /**
     * @param string $type Type of calculator
     * @param int $storeId
     * @param \Closure $billingAddress
     * @param \Closure $shippingAddress
     * @param null|int $customerTaxClassId
     * @param null|int $customerId
     * @param AbstractCalculator $expectedInstanceType
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
        if ($billingAddress!=null) {
            $billingAddress = $billingAddress($this);
        }
        if ($shippingAddress!=null) {
            $shippingAddress = $shippingAddress($this);
        }
        $instanceMock = $this->getMockBuilder($expectedInstanceType)
            ->disableOriginalConstructor()
            ->getMock();
        $objectManagerMock = $this->getMockForAbstractClass(ObjectManagerInterface::class);

        // Verify create() is called with correct concrete type
        $objectManagerMock->expects($this->once())
            ->method('create')
            ->with($expectedInstanceType, ['storeId' => $storeId])
            ->willReturn($instanceMock);

        /** @var CalculatorFactory $calculatorFactory */
        $calculatorFactory = $this->objectManager->getObject(
            CalculatorFactory::class,
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

    protected function getMockForAddress() {
        $address = $this->getMockBuilder(\Magento\Customer\Api\Data\AddressInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        return $address;
    }

    /**
     * Returns a set of 'true' and 'false' parameters for each of the setter/getter method pairs
     *
     * @return array
     */
    public static function createDataProvider()
    {
        $billingAddressMock = static fn (self $testCase) => $testCase->getMockForAddress();
        $shippingAddressMock = static fn (self $testCase) => $testCase->getMockForAddress();

        return [
            'Unit' => [
                CalculatorFactory::CALC_UNIT_BASE,
                1,
                null,
                null,
                null,
                null,
                UnitBaseCalculator::class,
            ],
            'Row HasBilling' => [
                CalculatorFactory::CALC_ROW_BASE,
                2,
                $billingAddressMock,
                null,
                null,
                null,
                RowBaseCalculator::class,
            ],
            'Row HasCustomerTaxClassId' => [
                CalculatorFactory::CALC_ROW_BASE,
                3,
                null,
                null,
                123,
                null,
                RowBaseCalculator::class,
            ],
            'Total HasShipping' => [
                CalculatorFactory::CALC_TOTAL_BASE,
                1,
                null,
                $shippingAddressMock,
                null,
                null,
                TotalBaseCalculator::class,
            ],
            'Total HasShipping HasBilling HasCustomerTaxClassId' => [
                CalculatorFactory::CALC_TOTAL_BASE,
                1,
                $billingAddressMock,
                $shippingAddressMock,
                1,
                null,
                TotalBaseCalculator::class,
            ],
            'Total HasShipping HasBilling HasCustomerTaxClassId, HasCustomer' => [
                CalculatorFactory::CALC_TOTAL_BASE,
                1,
                $billingAddressMock,
                $shippingAddressMock,
                1,
                1,
                TotalBaseCalculator::class,
            ],
        ];
    }

    public function testCreateInvalid()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown calculation type: NOT_A_TYPE');
        /** @var CalculatorFactory $calculatorFactory */
        $calculatorFactory = $this->objectManager->getObject(
            CalculatorFactory::class
        );

        // Call create() with a bad type to generate exception
        $calculatorFactory->create('NOT_A_TYPE', 1);
    }
}
