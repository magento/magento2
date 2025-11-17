<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Quote\Test\Unit\Observer\Frontend\Quote\Address;

use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Customer\Api\Data\GroupInterface;
use Magento\Customer\Api\GroupManagementInterface;
use Magento\Customer\Helper\Address as CustomerAddress;
use Magento\Customer\Model\Session;
use Magento\Customer\Model\Vat;
use Magento\Framework\Event\Observer;
use Magento\Quote\Test\Unit\Helper\ObserverTestHelper;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Api\Data\ShippingInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Observer\Frontend\Quote\Address\CollectTotalsObserver;
use Magento\Quote\Observer\Frontend\Quote\Address\VatValidator;
use Magento\Quote\Test\Unit\Helper\CustomerInterfaceFactoryTestHelper;
use Magento\Quote\Test\Unit\Helper\QuoteAddressTestHelper;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class CollectTotalsTest
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CollectTotalsObserverTest extends TestCase
{
    /**
     * @var CollectTotalsObserver
     */
    protected $model;

    /**
     * @var MockObject
     */
    protected $customerAddressMock;

    /**
     * @var MockObject
     */
    protected $customerSession;

    /**
     * @var MockObject
     */
    protected $customerVatMock;

    /**
     * @var MockObject
     */
    protected $addressRepository;

    /**
     * @var MockObject
     */
    protected $quoteAddressMock;

    /**
     * @var MockObject
     */
    protected $quoteMock;

    /**
     * @var MockObject
     */
    protected $storeId;

    /**
     * @var MockObject
     */
    protected $customerMock;

    /**
     * @var MockObject
     */
    protected $vatValidatorMock;

    /**
     * @var MockObject
     */
    protected $observerMock;

    /**
     * @var MockObject
     */
    protected $customerDataFactoryMock;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var MockObject
     */
    protected $groupManagementMock;

    /**
     * @var MockObject
     */
    protected $groupInterfaceMock;

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->storeId = 1;
        $this->customerMock = $this->createMock(CustomerInterface::class);
        $this->customerAddressMock = $this->createMock(CustomerAddress::class);
        $this->customerVatMock = $this->createMock(Vat::class);
        $this->customerDataFactoryMock = $this->getMockBuilder(CustomerInterfaceFactoryTestHelper::class)
            ->onlyMethods(['create', 'mergeDataObjectWithArray'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->vatValidatorMock = $this->createMock(VatValidator::class);
        $this->observerMock = new ObserverTestHelper();

        $this->quoteAddressMock = $this->getMockBuilder(QuoteAddressTestHelper::class)
            ->onlyMethods(['getCountryId', 'getVatId', 'getQuote', '__wakeup', 'setPrevQuoteCustomerGroupId'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->quoteMock = $this->getMockBuilder(\Magento\Quote\Test\Unit\Helper\QuoteTestHelper::class)
            ->onlyMethods(['getCustomerGroupId', 'getCustomer', '__wakeup', 'setCustomer', 'setCustomerGroupId'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->groupManagementMock = $this->createMock(GroupManagementInterface::class);

        $this->groupInterfaceMock = $this->createMock(GroupInterface::class);

        $shippingAssignmentMock = $this->createMock(ShippingAssignmentInterface::class);
        $shippingMock = $this->createMock(ShippingInterface::class);

        $shippingAssignmentMock->expects($this->once())->method('getShipping')->willReturn($shippingMock);
        $shippingMock->expects($this->once())->method('getAddress')->willReturn($this->quoteAddressMock);

        $this->observerMock->setShippingAssignment($shippingAssignmentMock);

        $this->observerMock->setQuote($this->quoteMock);
        $this->quoteMock->method('getCustomer')->willReturn($this->customerMock);
        $this->addressRepository = $this->createMock(AddressRepositoryInterface::class);
        $this->customerSession = $this->getMockBuilder(Session::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->customerMock->method('getStoreId')->willReturn($this->storeId);

        $this->model = new CollectTotalsObserver(
            $this->customerAddressMock,
            $this->customerVatMock,
            $this->vatValidatorMock,
            $this->customerDataFactoryMock,
            $this->groupManagementMock,
            $this->addressRepository,
            $this->customerSession
        );
    }

    public function testDispatchWithDisableVatValidator()
    {
        $this->vatValidatorMock->expects($this->once())
            ->method('isEnabled')
            ->with($this->quoteAddressMock, $this->storeId)
            ->willReturn(false);
        $this->model->execute($this->observerMock);
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function testDispatchWithCustomerCountryNotInEUAndNotLoggedCustomerInGroup()
    {
        $this->groupManagementMock->expects($this->once())
            ->method('getNotLoggedInGroup')
            ->willReturn($this->groupInterfaceMock);
        $this->groupInterfaceMock->expects($this->once())
            ->method('getId')->willReturn(null);
        $this->vatValidatorMock->expects($this->once())
            ->method('isEnabled')
            ->with($this->quoteAddressMock, $this->storeId)
            ->willReturn(true);

        $this->quoteAddressMock->expects($this->once())
            ->method('getCountryId')
            ->willReturn('customerCountryCode');
        $this->quoteAddressMock->expects($this->once())->method('getVatId')->willReturn('vatId');

        $this->customerVatMock->expects(
            $this->once()
        )->method(
            'isCountryInEU'
        )->with(
            'customerCountryCode'
        )->willReturn(
            false
        );

        $this->customerMock->expects($this->once())->method('getId')->willReturn(null);

        /** Assertions */
        $this->quoteAddressMock->expects($this->never())->method('setPrevQuoteCustomerGroupId');
        $this->customerDataFactoryMock->expects($this->never())->method('mergeDataObjectWithArray');
        $this->quoteMock->expects($this->never())->method('setCustomerGroupId');

        /** SUT execution */
        $this->model->execute($this->observerMock);
    }

    public function testDispatchWithDefaultCustomerGroupId()
    {
        $this->vatValidatorMock->expects($this->once())
            ->method('isEnabled')
            ->with($this->quoteAddressMock, $this->storeId)
            ->willReturn(true);

        $this->quoteAddressMock->expects($this->once())
            ->method('getCountryId')
            ->willReturn('customerCountryCode');
        $this->quoteAddressMock->expects($this->once())->method('getVatId')->willReturn(null);

        $this->quoteMock->expects($this->exactly(2))
            ->method('getCustomerGroupId')
            ->willReturn('customerGroupId');
        $this->customerMock->expects($this->once())->method('getId')->willReturn('1');

        /** Assertions */
        $this->quoteAddressMock->expects($this->once())
            ->method('setPrevQuoteCustomerGroupId')
            ->with('customerGroupId');
        $this->customerDataFactoryMock->method('create')->willReturn($this->customerMock);

        $this->quoteMock->expects($this->once())->method('setCustomer')->with($this->customerMock);
        /** SUT execution */
        $this->model->execute($this->observerMock);
    }

    public function testDispatchWithCustomerCountryInEU()
    {
        $this->vatValidatorMock->expects($this->once())
            ->method('isEnabled')
            ->with($this->quoteAddressMock, $this->storeId)
            ->willReturn(true);

        $this->quoteAddressMock->expects($this->once())
            ->method('getCountryId')
            ->willReturn('customerCountryCode');
        $this->quoteAddressMock->expects($this->once())
            ->method('getVatId')
            ->willReturn('vatID');

        $this->customerVatMock->expects($this->once())
            ->method('isCountryInEU')
            ->with('customerCountryCode')
            ->willReturn(true);

        $this->quoteMock->expects($this->once())
            ->method('getCustomerGroupId')
            ->willReturn('customerGroupId');

        $validationResult = ['some' => 'result'];
        $this->vatValidatorMock->expects($this->once())
            ->method('validate')
            ->with($this->quoteAddressMock, $this->storeId)
            ->willReturn($validationResult);

        $this->customerVatMock->expects($this->once())
            ->method('getCustomerGroupIdBasedOnVatNumber')
            ->with('customerCountryCode', $validationResult, $this->storeId)
            ->willReturn('customerGroupId');

        /** Assertions */
        $this->quoteAddressMock->expects($this->once())
            ->method('setPrevQuoteCustomerGroupId')
            ->with('customerGroupId');

        $this->quoteMock->expects($this->once())->method('setCustomerGroupId')->with('customerGroupId');
        $this->quoteMock->expects($this->once())->method('setCustomer')->with($this->customerMock);
        $this->customerDataFactoryMock->method('create')->willReturn($this->customerMock);
        $this->model->execute($this->observerMock);
    }

    public function testDispatchWithAddressCustomerVatIdAndCountryId()
    {
        $customerCountryCode = "BE";
        $customerVat = "123123123";
        $defaultShipping = 1;

        $customerAddress = $this->createMock(Address::class);
        $customerAddress->method('getVatId')->willReturn($customerVat);

        $customerAddress->method('getCountryId')->willReturn($customerCountryCode);

        $this->addressRepository->expects($this->once())
            ->method("getById")
            ->with($defaultShipping)
            ->willReturn($customerAddress);

        $this->customerMock->expects($this->atLeastOnce())
            ->method("getDefaultShipping")
            ->willReturn($defaultShipping);

        $this->vatValidatorMock->expects($this->once())
            ->method('isEnabled')
            ->with($this->quoteAddressMock, $this->storeId)
            ->willReturn(true);

        $this->customerVatMock->expects($this->once())
            ->method('isCountryInEU')
            ->with($customerCountryCode)
            ->willReturn(true);

        $this->model->execute($this->observerMock);
    }

    public function testDispatchWithEmptyShippingAddress()
    {
        $customerCountryCode = "DE";
        $customerVat = "123123123";
        $defaultShipping = 1;
        $customerAddress = $this->createMock(AddressInterface::class);

        $customerAddress->expects($this->once())
            ->method("getCountryId")
            ->willReturn($customerCountryCode);

        $customerAddress->expects($this->once())
            ->method("getVatId")
            ->willReturn($customerVat);
        $this->addressRepository->expects($this->once())
            ->method("getById")
            ->with($defaultShipping)
            ->willReturn($customerAddress);

        $this->customerMock->expects($this->atLeastOnce())
            ->method("getDefaultShipping")
            ->willReturn($defaultShipping);

        $this->vatValidatorMock->expects($this->once())
            ->method('isEnabled')
            ->with($this->quoteAddressMock, $this->storeId)
            ->willReturn(true);

        $this->quoteAddressMock->expects($this->once())
            ->method('getCountryId')
            ->willReturn(null);
        $this->quoteAddressMock->expects($this->once())
            ->method('getVatId')
            ->willReturn(null);

        $this->customerVatMock->expects($this->once())
            ->method('isCountryInEU')
            ->with($customerCountryCode)
            ->willReturn(true);

        $this->quoteMock->expects($this->once())
            ->method('getCustomerGroupId')
            ->willReturn('customerGroupId');
        $validationResult = ['some' => 'result'];
        $this->customerVatMock->expects($this->once())
            ->method('getCustomerGroupIdBasedOnVatNumber')
            ->with($customerCountryCode, $validationResult, $this->storeId)
            ->willReturn('customerGroupId');
        $this->customerSession->expects($this->once())
            ->method("setCustomerGroupId")
            ->with('customerGroupId');

        $this->vatValidatorMock->expects($this->once())
            ->method('validate')
            ->with($this->quoteAddressMock, $this->storeId)
            ->willReturn($validationResult);

        /** Assertions */
        $this->quoteAddressMock->expects($this->once())
            ->method('setPrevQuoteCustomerGroupId')
            ->with('customerGroupId');

        $this->quoteMock->expects($this->once())->method('setCustomerGroupId')->with('customerGroupId');
        $this->quoteMock->expects($this->once())->method('setCustomer')->with($this->customerMock);
        $this->customerDataFactoryMock->method('create')->willReturn($this->customerMock);
        $this->model->execute($this->observerMock);
    }
}
