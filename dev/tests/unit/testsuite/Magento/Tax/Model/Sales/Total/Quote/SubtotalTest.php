<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Model\Sales\Total\Quote;

/**
 * Test class for \Magento\Tax\Model\Sales\Total\Quote\Subtotal
 */
use Magento\TestFramework\Helper\ObjectManager;

class SubtotalTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\TestFramework\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $taxCalculationMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $taxConfigMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteDetailsBuilder;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $addressMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $keyBuilderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeMock;

    /**
     * @var \Magento\Tax\Model\Sales\Total\Quote\Subtotal
     */
    protected $model;

    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);

        $this->taxConfigMock = $this->getMockBuilder('\Magento\Tax\Model\Config')
            ->disableOriginalConstructor()
            ->setMethods(['priceIncludesTax', 'getShippingTaxClass', 'shippingPriceIncludesTax', 'discountTax'])
            ->getMock();
        $this->taxCalculationMock = $this->getMockBuilder('Magento\Tax\Api\TaxCalculationInterface')
            ->getMockForAbstractClass();
        $this->quoteDetailsBuilder = $this->getMockBuilder('\Magento\Tax\Api\Data\QuoteDetailsDataBuilder')
            ->disableOriginalConstructor()
            ->setMethods([
                'getItemBuilder', 'getAddressBuilder', 'getTaxClassKeyBuilder', 'create',
                'setBillingAddress', 'setShippingAddress', 'setCustomerTaxClassKey',
                'setItems', 'setCustomerId',
            ])->getMock();
        $this->keyBuilderMock = $this->getMock(
            'Magento\Tax\Api\Data\TaxClassKeyDataBuilder',
            ['setType', 'setValue', 'create'],
            [],
            '',
            false
        );
        $customerAddressBuilderMock = $this->getMock(
            'Magento\Customer\Api\Data\AddressDataBuilder',
            ['setCountryId', 'setRegion', 'setPostcode', 'setCity', 'setStreet', 'create'],
            [],
            '',
            false
        );
        $customerAddressRegionBuilderMock = $this->getMock(
            'Magento\Customer\Api\Data\RegionDataBuilder',
            ['setRegionId', 'create'],
            [],
            '',
            false
        );
        $customerAddressRegionBuilderMock->expects($this->any())->method('setRegionId')->willReturnSelf();

        $this->model = $this->objectManager->getObject(
            'Magento\Tax\Model\Sales\Total\Quote\Subtotal',
            [
                'taxConfig' => $this->taxConfigMock,
                'taxCalculationService' => $this->taxCalculationMock,
                'quoteDetailsBuilder' => $this->quoteDetailsBuilder,
                'taxClassKeyBuilder' => $this->keyBuilderMock,
                'customerAddressBuilder' => $customerAddressBuilderMock,
                'customerAddressRegionBuilder' => $customerAddressRegionBuilderMock,
            ]
        );

        $this->addressMock = $this->getMockBuilder('\Magento\Sales\Model\Quote\Address')
            ->disableOriginalConstructor()
            ->setMethods([
                'getAssociatedTaxables', 'getQuote', 'getBillingAddress',
                'getRegionId', 'getAllItems', '__wakeup',
                'getParentItem',
            ])->getMock();

        $this->quoteMock = $this->getMockBuilder('\Magento\Sales\Model\Quote')
            ->disableOriginalConstructor()
            ->getMock();

        $this->storeMock = $this->getMockBuilder('\Magento\Store\Model\Store')->disableOriginalConstructor()->getMock();
        $this->quoteMock->expects($this->any())->method('getStore')->willReturn($this->storeMock);
        $this->storeMock->expects($this->any())->method('getStoreId')->willReturn(111);
    }

    public function testCollectEmptyAddresses()
    {
        $this->addressMock->expects($this->once())->method('getAllItems')->willReturn(null);
        $this->taxConfigMock->expects($this->never())->method('priceIncludesTax');
        $this->model->collect($this->addressMock);
    }

    public function testCollect()
    {
        $priceIncludesTax = true;

        $this->checkGetAddressItems();
        $this->taxConfigMock->expects($this->once())->method('priceIncludesTax')->willReturn($priceIncludesTax);
        $this->addressMock->expects($this->atLeastOnce())->method('getParentItem')->willReturnSelf();
        $taxDetailsMock = $this->getMockBuilder('\Magento\Tax\Api\Data\TaxDetailsInterface')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->taxCalculationMock->expects($this->atLeastOnce())->method('calculateTax')->willReturn($taxDetailsMock);
        $taxDetailsMock->expects($this->atLeastOnce())->method('getItems')->willReturn([]);
        $this->model->collect($this->addressMock);
    }

    /**
     * Mock checks for $this->_getAddressItems() call
     */
    protected function checkGetAddressItems()
    {
        $customerTaxClassId = 2425;
        $this->addressMock->expects($this->atLeastOnce())
            ->method('getAllItems')->willReturn([$this->addressMock]);

        // calls in populateAddressData()
        $this->quoteDetailsBuilder->expects($this->atLeastOnce())->method('setBillingAddress');
        $this->quoteDetailsBuilder->expects($this->atLeastOnce())->method('setShippingAddress');

        $this->addressMock->expects($this->atLeastOnce())->method('getQuote')->willReturn($this->quoteMock);
        $this->quoteMock->expects($this->atLeastOnce())
            ->method('getCustomerTaxClassId')
            ->willReturn($customerTaxClassId);
        $this->quoteMock->expects($this->atLeastOnce())->method('getBillingAddress')->willReturn($this->addressMock);

        $this->keyBuilderMock->expects($this->atLeastOnce())->method('setType');
        $this->keyBuilderMock->expects($this->atLeastOnce())->method('setValue');
        $this->keyBuilderMock->expects($this->atLeastOnce())->method('create')->willReturn('taxClassKey');

        $this->quoteDetailsBuilder->expects($this->atLeastOnce())
            ->method('setCustomerTaxClassKey')
            ->with('taxClassKey');
        $this->quoteDetailsBuilder->expects($this->atLeastOnce())->method('setItems')->with([]);
        $this->quoteDetailsBuilder->expects($this->atLeastOnce())->method('setCustomerId');
        $quoteDetailsMock = $this->getMockBuilder('\Magento\Tax\Api\Data\QuoteDetailsInterface')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->quoteDetailsBuilder->expects($this->atLeastOnce())->method('create')->willReturn($quoteDetailsMock);
    }
}
