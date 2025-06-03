<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Tax\Test\Unit\Model\Sales\Total\Quote;

use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\Data\AddressInterfaceFactory;
use Magento\Customer\Api\Data\RegionInterface;
use Magento\Customer\Api\Data\RegionInterfaceFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Api\Data\ShippingInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\Address\Total;
use Magento\Store\Model\Store;
use Magento\Tax\Api\Data\QuoteDetailsInterface;
use Magento\Tax\Api\Data\QuoteDetailsInterfaceFactory;
use Magento\Tax\Api\Data\TaxClassKeyInterface;
use Magento\Tax\Api\Data\TaxClassKeyInterfaceFactory;
use Magento\Tax\Api\Data\TaxDetailsInterface;
use Magento\Tax\Api\TaxCalculationInterface;
use Magento\Tax\Model\Config;
use Magento\Tax\Model\Sales\Total\Quote\Subtotal;
use PHPUnit\Framework\MockObject\MockObject;
/**
 * Test class for \Magento\Tax\Model\Sales\Total\Quote\Subtotal
 */
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SubtotalTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var MockObject
     */
    protected $taxCalculationMock;

    /**
     * @var MockObject
     */
    protected $taxConfigMock;

    /**
     * @var MockObject
     */
    protected $quoteDetailsDataObjectFactoryMock;

    /**
     * @var MockObject
     */
    protected $addressMock;

    /**
     * @var MockObject
     */
    protected $keyDataObjectFactoryMock;

    /**
     * @var MockObject
     */
    protected $quoteMock;

    /**
     * @var MockObject
     */
    protected $storeMock;

    /**
     * @var MockObject
     */
    protected $shippingAssignmentMock;

    /**
     * @var MockObject
     */
    protected $totalsMock;

    /**
     * @var MockObject
     */
    protected $shippingMock;

    /**
     * @var Subtotal
     */
    protected $model;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->totalsMock = $this->createMock(Total::class);
        $this->shippingAssignmentMock = $this->getMockForAbstractClass(ShippingAssignmentInterface::class);
        $this->shippingMock = $this->getMockForAbstractClass(ShippingInterface::class);
        $this->taxConfigMock = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['priceIncludesTax', 'getShippingTaxClass', 'shippingPriceIncludesTax', 'discountTax'])
            ->getMock();
        $this->taxCalculationMock = $this->getMockBuilder(TaxCalculationInterface::class)
            ->getMockForAbstractClass();
        $this->quoteDetailsDataObjectFactoryMock =
            $this->getMockBuilder(QuoteDetailsInterfaceFactory::class)
                ->disableOriginalConstructor()
                ->addMethods(['setBillingAddress', 'setShippingAddress'])
                ->onlyMethods(['create'])->getMock();
        $this->keyDataObjectFactoryMock = $this->createPartialMock(
            TaxClassKeyInterfaceFactory::class,
            ['create']
        );

        $customerAddressMock = $this->getMockForAbstractClass(
            AddressInterface::class,
            [],
            '',
            false
        );
        $customerAddressFactoryMock = $this->createPartialMock(
            AddressInterfaceFactory::class,
            ['create']
        );
        $customerAddressFactoryMock->expects($this->any())->method('create')->willReturn($customerAddressMock);

        $customerAddressRegionMock = $this->getMockForAbstractClass(
            RegionInterface::class,
            [],
            '',
            false
        );
        $customerAddressRegionMock->expects($this->any())->method('setRegionId')->willReturnSelf();
        $customerAddressRegionFactoryMock = $this->createPartialMock(
            RegionInterfaceFactory::class,
            ['create']
        );
        $customerAddressRegionFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($customerAddressRegionMock);

        $this->model = $this->objectManager->getObject(
            Subtotal::class,
            [
                'taxConfig' => $this->taxConfigMock,
                'taxCalculationService' => $this->taxCalculationMock,
                'quoteDetailsDataObjectFactory' => $this->quoteDetailsDataObjectFactoryMock,
                'taxClassKeyDataObjectFactory' => $this->keyDataObjectFactoryMock,
                'customerAddressFactory' => $customerAddressFactoryMock,
                'customerAddressRegionFactory' => $customerAddressRegionFactoryMock,
            ]
        );

        $this->addressMock = $this->getMockBuilder(Address::class)
            ->disableOriginalConstructor()
            ->addMethods([
                'getAssociatedTaxables', 'getBillingAddress',
                'getParentItem',
            ])
            ->onlyMethods([
                'getQuote', 'getRegionId', 'getAllItems', '__wakeup'])->getMock();

        $this->quoteMock = $this->getMockBuilder(Quote::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->addressMock->expects($this->any())->method('getQuote')->willReturn($this->quoteMock);
        $this->storeMock = $this->getMockBuilder(
            Store::class
        )->disableOriginalConstructor()
            ->addMethods(['getStoreId'])->getMock();
        $this->quoteMock->expects($this->any())->method('getStore')->willReturn($this->storeMock);
        $this->storeMock->expects($this->any())->method('getStoreId')->willReturn(111);
    }

    public function testCollectEmptyAddresses()
    {
        $this->shippingAssignmentMock->expects($this->once())->method('getItems')->willReturn(null);
        $this->taxConfigMock->expects($this->never())->method('priceIncludesTax');
        $this->model->collect($this->quoteMock, $this->shippingAssignmentMock, $this->totalsMock);
    }

    public function testCollect()
    {
        $priceIncludesTax = true;

        $this->checkGetAddressItems();
        $this->taxConfigMock->expects($this->once())->method('priceIncludesTax')->willReturn($priceIncludesTax);
        $this->addressMock->expects($this->atLeastOnce())->method('getParentItem')->willReturnSelf();
        $taxDetailsMock = $this->getMockBuilder(TaxDetailsInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->taxCalculationMock->expects($this->atLeastOnce())->method('calculateTax')->willReturn($taxDetailsMock);
        $taxDetailsMock->expects($this->atLeastOnce())->method('getItems')->willReturn([]);
        $this->model->collect($this->quoteMock, $this->shippingAssignmentMock, $this->totalsMock);
    }

    /**
     * Mock checks for $this->_getAddressItems() call
     */
    protected function checkGetAddressItems()
    {
        $customerTaxClassId = 2425;
        $this->shippingAssignmentMock->expects($this->atLeastOnce())
            ->method('getItems')->willReturn([$this->addressMock]);
        $this->shippingAssignmentMock->expects($this->any())->method('getShipping')->willReturn($this->shippingMock);

        $this->quoteMock->expects($this->atLeastOnce())
            ->method('getCustomerTaxClassId')
            ->willReturn($customerTaxClassId);
        $this->quoteMock->expects($this->atLeastOnce())->method('getBillingAddress')->willReturn($this->addressMock);
        $this->shippingMock->expects($this->any())->method('getAddress')->willReturn($this->addressMock);
        $keyDataObjectMock = $this->getMockBuilder(TaxClassKeyInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->keyDataObjectFactoryMock->expects($this->atLeastOnce())->method('create')
            ->willReturn($keyDataObjectMock);
        $keyDataObjectMock->expects($this->atLeastOnce())->method('setType')->willReturnSelf();
        $keyDataObjectMock->expects($this->atLeastOnce())->method('setValue')->willReturnSelf();

        $quoteDetailsMock = $this->getMockBuilder(QuoteDetailsInterface::class)
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
