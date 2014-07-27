<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Tax\Model\Calculation;

use Magento\Customer\Service\V1\Data\Address;
use Magento\TestFramework\Helper\ObjectManager;

/**
 * Test class for \Magento\Tax\Model\CalculatorFactory
 */
class CalculatorFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\TestFramework\Helper\ObjectManager
     */
    public $objectManager;

    public function setUp()
    {
        $this->objectManager = new ObjectManager($this);
    }

    /**
     * @param string $type Type of calculator
     * @param int $storeId
     * @param Address $billingAddress
     * @param Address $shippingAddress
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
        $objectManagerMock = $this->getMockBuilder('Magento\Framework\ObjectManager')->getMock();

        // Verify create() is called with correct concrete type
        $objectManagerMock->expects($this->once())
            ->method('create')
            ->with($expectedInstanceType, ['storeId' => $storeId])
            ->will($this->returnValue($instanceMock));

        /** @var CalculatorFactory $calculatorFactory */
        $calculatorFactory = $this->objectManager->getObject(
            'Magento\Tax\Model\Calculation\CalculatorFactory',
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
        $billingAddressMock = $this->getMockBuilder('\Magento\Customer\Service\V1\Data\Address')
            ->disableOriginalConstructor()
            ->getMock();
        $shippingAddressMock = $this->getMockBuilder('\Magento\Customer\Service\V1\Data\Address')
            ->disableOriginalConstructor()
            ->getMock();

        return [
            'Unit' => [
                CalculatorFactory::CALC_UNIT_BASE,
                1,
                null,
                null,
                null,
                null,
                'Magento\Tax\Model\Calculation\UnitBaseCalculator'
            ],
            'Row HasBilling' => [
                CalculatorFactory::CALC_ROW_BASE,
                2,
                $billingAddressMock,
                null,
                null,
                null,
                'Magento\Tax\Model\Calculation\RowBaseCalculator'
            ],
            'Row HasCustomerTaxClassId' => [
                CalculatorFactory::CALC_ROW_BASE,
                3,
                null,
                null,
                123,
                null,
                'Magento\Tax\Model\Calculation\RowBaseCalculator'
            ],
            'Total HasShipping' => [
                CalculatorFactory::CALC_TOTAL_BASE,
                1,
                null,
                $shippingAddressMock,
                null,
                null,
                'Magento\Tax\Model\Calculation\TotalBaseCalculator'
            ],
            'Total HasShipping HasBilling HasCustomerTaxClassId' => [
                CalculatorFactory::CALC_TOTAL_BASE,
                1,
                $billingAddressMock,
                $shippingAddressMock,
                1,
                null,
                'Magento\Tax\Model\Calculation\TotalBaseCalculator'
            ],
            'Total HasShipping HasBilling HasCustomerTaxClassId, HasCustomer' => [
                CalculatorFactory::CALC_TOTAL_BASE,
                1,
                $billingAddressMock,
                $shippingAddressMock,
                1,
                1,
                'Magento\Tax\Model\Calculation\TotalBaseCalculator'
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
            'Magento\Tax\Model\Calculation\CalculatorFactory'
        );

        // Call create() with a bad type to generate exception
        $calculatorFactory->create('NOT_A_TYPE', 1);
    }
}
