<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Tax\Test\Unit\Model\Sales\Total\Quote;

use \Magento\Tax\Model\Sales\Total\Quote\Shipping;

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
     * @var Shipping
     */
    private $model;

    protected function setUp()
    {
        $this->taxConfigMock = $this->getMock('Magento\Tax\Model\Config', [], [], '', false);
        $this->taxCalculationMock = $this->getMock('Magento\Tax\Api\TaxCalculationInterface');
        $this->quoteDetailsDataObjectFactory = $this->getMock('Magento\Tax\Api\Data\QuoteDetailsInterfaceFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->itemDetailsDataObjectFactory = $this->getMock('Magento\Tax\Api\Data\QuoteDetailsItemInterfaceFactory',
            [
                'create',
            ],
            [],
            '',
            false
        );
        $this->taxClassKeyDataObjectFactory = $this->getMock('Magento\Tax\Api\Data\TaxClassKeyInterfaceFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->addressFactoryMock = $this->getMock('Magento\Customer\Api\Data\AddressInterfaceFactory',
            [],
            [],
            '',
            false
        );
        $this->regionFactoryMock = $this->getMock('Magento\Customer\Api\Data\RegionInterfaceFactory',
            [],
            [],
            '',
            false
        );
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
        $storeMock = $this->getMockObject('Magento\Store\Model\Store', [
            'store_id' => $storeId,
        ]);
        $quoteMock = $this->getMockObject(
            'Magento\Quote\Model\Quote',
            [
                'store' => $storeMock,
            ]
        );
        $addressMock = $this->getMockObject('Magento\Quote\Model\Quote\Address', [
            'all_items' => [],
            'shipping_tax_calculation_amount' => 100,
            'base_shipping_tax_calculation_amount' => 200,
            'shipping_discount_amount' => 10,
            'base_shipping_discount_amount' => 20,
            'quote' => $quoteMock,
        ]);
        $this->taxCalculationMock->expects($this->never())->method('calculateTax');
        $this->model->collect($addressMock);
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
}
