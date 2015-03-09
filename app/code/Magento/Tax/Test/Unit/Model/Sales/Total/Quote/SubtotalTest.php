<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Test\Unit\Model\Sales\Total\Quote;

/**
 * Test class for \Magento\Tax\Model\Sales\Total\Quote\Subtotal
 */
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class SubtotalTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
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
    protected $quoteDetailsDataObjectFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $addressMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $keyDataObjectFactoryMock;

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
        $this->quoteDetailsDataObjectFactoryMock =
            $this->getMockBuilder('\Magento\Tax\Api\Data\QuoteDetailsInterfaceFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])->getMock();
        $this->keyDataObjectFactoryMock = $this->getMock(
            'Magento\Tax\Api\Data\TaxClassKeyInterfaceFactory',
            ['create'],
            [],
            '',
            false
        );

        $customerAddressMock = $this->getMockForAbstractClass(
            'Magento\Customer\Api\Data\AddressInterface',
            [],
            '',
            false
        );
        $customerAddressFactoryMock = $this->getMock(
            'Magento\Customer\Api\Data\AddressInterfaceFactory',
            ['create'],
            [],
            '',
            false
        );
        $customerAddressFactoryMock->expects($this->any())->method('create')->willReturn($customerAddressMock);

        $customerAddressRegionMock = $this->getMockForAbstractClass(
            'Magento\Customer\Api\Data\RegionInterface',
            [],
            '',
            false
        );
        $customerAddressRegionMock->expects($this->any())->method('setRegionId')->willReturnSelf();
        $customerAddressRegionFactoryMock = $this->getMock(
            'Magento\Customer\Api\Data\RegionInterfaceFactory',
            ['create'],
            [],
            '',
            false
        );
        $customerAddressRegionFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($customerAddressRegionMock);

        $this->model = $this->objectManager->getObject(
            'Magento\Tax\Model\Sales\Total\Quote\Subtotal',
            [
                'taxConfig' => $this->taxConfigMock,
                'taxCalculationService' => $this->taxCalculationMock,
                'quoteDetailsDataObjectFactory' => $this->quoteDetailsDataObjectFactoryMock,
                'taxClassKeyDataObjectFactory' => $this->keyDataObjectFactoryMock,
                'customerAddressFactory' => $customerAddressFactoryMock,
                'customerAddressRegionFactory' => $customerAddressRegionFactoryMock,
            ]
        );

        $this->addressMock = $this->getMockBuilder('\Magento\Quote\Model\Quote\Address')
            ->disableOriginalConstructor()
            ->setMethods([
                'getAssociatedTaxables', 'getQuote', 'getBillingAddress',
                'getRegionId', 'getAllItems', '__wakeup',
                'getParentItem',
            ])->getMock();

        $this->quoteMock = $this->getMockBuilder('\Magento\Quote\Model\Quote')
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

        $this->addressMock->expects($this->atLeastOnce())->method('getQuote')->willReturn($this->quoteMock);
        $this->quoteMock->expects($this->atLeastOnce())
            ->method('getCustomerTaxClassId')
            ->willReturn($customerTaxClassId);
        $this->quoteMock->expects($this->atLeastOnce())->method('getBillingAddress')->willReturn($this->addressMock);

        $keyDataObjectMock = $this->getMockBuilder('\Magento\Tax\Api\Data\TaxClassKeyInterface')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->keyDataObjectFactoryMock->expects($this->atLeastOnce())->method('create')
            ->willReturn($keyDataObjectMock);
        $keyDataObjectMock->expects($this->atLeastOnce())->method('setType')->willReturnSelf();
        $keyDataObjectMock->expects($this->atLeastOnce())->method('setValue')->willReturnSelf();

        $quoteDetailsMock = $this->getMockBuilder('\Magento\Tax\Api\Data\QuoteDetailsInterface')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->quoteDetailsDataObjectFactoryMock->expects($this->atLeastOnce())
            ->method('create')->willReturn($quoteDetailsMock);
        // calls in populateAddressData()
        $quoteDetailsMock->expects($this->atLeastOnce())->method('setBillingAddress')->willReturnSelf();
        $quoteDetailsMock->expects($this->atLeastOnce())->method('setShippingAddress')->willReturnSelf();
        $quoteDetailsMock->expects($this->atLeastOnce())
            ->method('setCustomerTaxClassKey')
            ->with($keyDataObjectMock)
            ->willReturnSelf();
        $quoteDetailsMock->expects($this->atLeastOnce())->method('setItems')->with([])->willReturnSelf();
        $quoteDetailsMock->expects($this->atLeastOnce())->method('setCustomerId')->willReturnSelf();
    }
}
