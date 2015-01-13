<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Model\Sales\Total\Quote;

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
    private $quoteDetailsBuilder;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $itemDetailsBuilder;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $taxClassKeyBuilder;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $addressBuilderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $regionBuilderMock;

    /**
     * @var Shipping
     */
    private $model;

    protected function setUp()
    {
        $this->taxConfigMock = $this->getMock('Magento\Tax\Model\Config', [], [], '', false);
        $this->taxCalculationMock = $this->getMock('Magento\Tax\Api\TaxCalculationInterface');
        $this->quoteDetailsBuilder = $this->getMock('Magento\Tax\Api\Data\QuoteDetailsDataBuilder',
            ['create'],
            [],
            '',
            false
        );
        $this->itemDetailsBuilder = $this->getMock('Magento\Tax\Api\Data\QuoteDetailsItemDataBuilder',
            [
                'setType',
                'setCode',
                'setQuantity',
                'setUnitPrice',
                'setDiscountAmount',
                'setTaxClassKey',
                'setTaxIncluded',
                'create',
            ],
            [],
            '',
            false
        );
        $this->taxClassKeyBuilder = $this->getMock('Magento\Tax\Api\Data\TaxClassKeyDataBuilder',
            ['setType', 'setValue', 'create'],
            [],
            '',
            false
        );
        $this->addressBuilderMock = $this->getMock('Magento\Customer\Api\Data\AddressDataBuilder',
            [],
            [],
            '',
            false
        );
        $this->regionBuilderMock = $this->getMock('Magento\Customer\Api\Data\RegionDataBuilder',
            [],
            [],
            '',
            false
        );
        $this->model = new Shipping(
            $this->taxConfigMock,
            $this->taxCalculationMock,
            $this->quoteDetailsBuilder,
            $this->itemDetailsBuilder,
            $this->taxClassKeyBuilder,
            $this->addressBuilderMock,
            $this->regionBuilderMock
        );
    }

    public function testCollectDoesNotCalculateTaxIfThereIsNoItemsRelatedToGivenAddress()
    {
        $storeId = 1;
        $storeMock = $this->getMockObject('Magento\Store\Model\Store', [
            'store_id' => $storeId,
        ]);
        $quoteMock = $this->getMockObject(
            'Magento\Sales\Model\Quote',
            [
                'store' => $storeMock,
            ]
        );
        $addressMock = $this->getMockObject('Magento\Sales\Model\Quote\Address', [
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
