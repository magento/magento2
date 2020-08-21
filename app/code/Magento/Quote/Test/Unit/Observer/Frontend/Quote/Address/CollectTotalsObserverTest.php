<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Test\Unit\Observer\Frontend\Quote\Address;

use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Customer\Api\Data\GroupInterface;
use Magento\Customer\Api\GroupManagementInterface;
use Magento\Customer\Helper\Address;
use Magento\Customer\Model\Session;
use Magento\Customer\Model\Vat;
use Magento\Framework\Event\Observer;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Api\Data\ShippingInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Observer\Frontend\Quote\Address\CollectTotalsObserver;
use Magento\Quote\Observer\Frontend\Quote\Address\VatValidator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

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
        $this->customerMock = $this->getMockForAbstractClass(
            CustomerInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['getStoreId', 'getCustomAttribute', 'getId', '__wakeup']
        );
        $this->customerAddressMock = $this->createMock(Address::class);
        $this->customerVatMock = $this->createMock(Vat::class);
        $this->customerDataFactoryMock = $this->getMockBuilder(CustomerInterfaceFactory::class)
            ->addMethods(['mergeDataObjectWithArray'])
            ->onlyMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->vatValidatorMock = $this->createMock(VatValidator::class);
        $this->observerMock = $this->getMockBuilder(Observer::class)
            ->addMethods(['getShippingAssignment', 'getQuote'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->quoteAddressMock = $this->getMockBuilder(\Magento\Quote\Model\Quote\Address::class)
            ->addMethods(['setPrevQuoteCustomerGroupId'])
            ->onlyMethods(['getCountryId', 'getVatId', 'getQuote', '__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->quoteMock = $this->getMockBuilder(Quote::class)
            ->addMethods(['setCustomerGroupId'])
            ->onlyMethods(['getCustomerGroupId', 'getCustomer', '__wakeup', 'setCustomer'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->groupManagementMock = $this->getMockForAbstractClass(
            GroupManagementInterface::class,
            [],
            '',
            false,
            true,
            true,
            [
                'getDefaultGroup',
                'getNotLoggedInGroup'
            ]
        );

        $this->groupInterfaceMock = $this->getMockForAbstractClass(
            GroupInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['getId']
        );

        $shippingAssignmentMock = $this->getMockForAbstractClass(ShippingAssignmentInterface::class);
        $shippingMock = $this->getMockForAbstractClass(ShippingInterface::class);
        $shippingAssignmentMock->expects($this->once())->method('getShipping')->willReturn($shippingMock);
        $shippingMock->expects($this->once())->method('getAddress')->willReturn($this->quoteAddressMock);

        $this->observerMock->expects($this->once())
            ->method('getShippingAssignment')
            ->willReturn($shippingAssignmentMock);

        $this->observerMock->expects($this->once())->method('getQuote')->willReturn($this->quoteMock);
        $this->quoteMock->expects($this->any())
            ->method('getCustomer')
            ->willReturn($this->customerMock);

        $this->addressRepository = $this->getMockForAbstractClass(AddressRepositoryInterface::class);
        $this->customerSession = $this->getMockBuilder(Session::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->customerMock->expects($this->any())->method('getStoreId')->willReturn($this->storeId);

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

        $this->quoteMock->expects($this->once())
            ->method('getCustomerGroupId')
            ->willReturn('customerGroupId');
        $this->customerMock->expects($this->exactly(2))->method('getId')->willReturn('1');
        $this->groupManagementMock->expects($this->once())
            ->method('getDefaultGroup')
            ->willReturn($this->groupInterfaceMock);
        $this->groupInterfaceMock->expects($this->once())
            ->method('getId')->willReturn('defaultCustomerGroupId');
        /** Assertions */
        $this->quoteAddressMock->expects($this->once())
            ->method('setPrevQuoteCustomerGroupId')
            ->with('customerGroupId');
        $this->quoteMock->expects($this->once())->method('setCustomerGroupId')->with('defaultCustomerGroupId');
        $this->customerDataFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->customerMock);

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

        $this->customerMock->expects($this->once())->method('getId')->willReturn('1');
        $this->quoteMock->expects($this->once())->method('setCustomerGroupId')->with('customerGroupId');
        $this->quoteMock->expects($this->once())->method('setCustomer')->with($this->customerMock);
        $this->customerDataFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->customerMock);
        $this->model->execute($this->observerMock);
    }

    public function testDispatchWithAddressCustomerVatIdAndCountryId()
    {
        $customerCountryCode = "BE";
        $customerVat = "123123123";
        $defaultShipping = 1;

        $customerAddress = $this->createMock(\Magento\Quote\Model\Quote\Address::class);
        $customerAddress->expects($this->any())
            ->method("getVatId")
            ->willReturn($customerVat);

        $customerAddress->expects($this->any())
            ->method("getCountryId")
            ->willReturn($customerCountryCode);

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

        $customerAddress = $this->getMockForAbstractClass(AddressInterface::class);
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

        $this->customerMock->expects($this->once())->method('getId')->willReturn('1');

        $this->quoteMock->expects($this->once())->method('setCustomerGroupId')->with('customerGroupId');
        $this->quoteMock->expects($this->once())->method('setCustomer')->with($this->customerMock);
        $this->customerDataFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->customerMock);
        $this->model->execute($this->observerMock);
    }
}
