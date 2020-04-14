<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Quote\Test\Unit\Observer\Frontend\Quote\Address;

use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Customer\Api\Data\GroupInterface;
use Magento\Customer\Api\GroupManagementInterface;
use Magento\Customer\Model\Session;
use Magento\Customer\Model\Vat;
use Magento\Framework\Event\Observer;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Api\Data\ShippingInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Observer\Frontend\Quote\Address\CollectTotalsObserver;
use Magento\Quote\Observer\Frontend\Quote\Address\VatValidator;
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
    protected function setUp()
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
        $this->customerAddressMock = $this->createMock(\Magento\Customer\Helper\Address::class);
        $this->customerVatMock = $this->createMock(Vat::class);
        $this->customerDataFactoryMock = $this->createPartialMock(
            CustomerInterfaceFactory::class,
            ['mergeDataObjectWithArray', 'create']
        );
        $this->vatValidatorMock = $this->createMock(VatValidator::class);
        $this->observerMock = $this->createPartialMock(
            Observer::class,
            ['getShippingAssignment', 'getQuote']
        );

        $this->quoteAddressMock = $this->createPartialMock(
            Address::class,
            ['getCountryId', 'getVatId', 'getQuote', 'setPrevQuoteCustomerGroupId', '__wakeup']
        );

        $this->quoteMock = $this->createPartialMock(
            Quote::class,
            ['setCustomerGroupId', 'getCustomerGroupId', 'getCustomer', '__wakeup', 'setCustomer']
        );

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

        $shippingAssignmentMock = $this->createMock(ShippingAssignmentInterface::class);
        $shippingMock = $this->createMock(ShippingInterface::class);
        $shippingAssignmentMock->expects($this->once())->method('getShipping')->willReturn($shippingMock);
        $shippingMock->expects($this->once())->method('getAddress')->willReturn($this->quoteAddressMock);

        $this->observerMock->expects($this->once())
            ->method('getShippingAssignment')
            ->willReturn($shippingAssignmentMock);

        $this->observerMock->expects($this->once())->method('getQuote')->willReturn($this->quoteMock);
        $this->quoteMock->expects($this->any())
            ->method('getCustomer')
            ->will($this->returnValue($this->customerMock));

        $this->addressRepository = $this->createMock(AddressRepositoryInterface::class);
        $this->customerSession = $this->getMockBuilder(Session::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->customerMock->expects($this->any())->method('getStoreId')->will($this->returnValue($this->storeId));

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
            ->will($this->returnValue(false));
        $this->model->execute($this->observerMock);
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function testDispatchWithCustomerCountryNotInEUAndNotLoggedCustomerInGroup()
    {
        $this->groupManagementMock->expects($this->once())
            ->method('getNotLoggedInGroup')
            ->will($this->returnValue($this->groupInterfaceMock));
        $this->groupInterfaceMock->expects($this->once())
            ->method('getId')->will($this->returnValue(null));
        $this->vatValidatorMock->expects($this->once())
            ->method('isEnabled')
            ->with($this->quoteAddressMock, $this->storeId)
            ->will($this->returnValue(true));

        $this->quoteAddressMock->expects($this->once())
            ->method('getCountryId')
            ->will($this->returnValue('customerCountryCode'));
        $this->quoteAddressMock->expects($this->once())->method('getVatId')->will($this->returnValue('vatId'));

        $this->customerVatMock->expects(
            $this->once()
        )->method(
            'isCountryInEU'
        )->with(
            'customerCountryCode'
        )->will(
            $this->returnValue(false)
        );

        $this->customerMock->expects($this->once())->method('getId')->will($this->returnValue(null));

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
            ->will($this->returnValue(true));

        $this->quoteAddressMock->expects($this->once())
            ->method('getCountryId')
            ->will($this->returnValue('customerCountryCode'));
        $this->quoteAddressMock->expects($this->once())->method('getVatId')->will($this->returnValue(null));

        $this->quoteMock->expects($this->exactly(2))
            ->method('getCustomerGroupId')
            ->will($this->returnValue('customerGroupId'));
        $this->customerMock->expects($this->once())->method('getId')->will($this->returnValue('1'));
        /** Assertions */
        $this->quoteAddressMock->expects($this->once())
            ->method('setPrevQuoteCustomerGroupId')
            ->with('customerGroupId');
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
            ->will($this->returnValue(true));

        $this->quoteAddressMock->expects($this->once())
            ->method('getCountryId')
            ->will($this->returnValue('customerCountryCode'));
        $this->quoteAddressMock->expects($this->once())
            ->method('getVatId')
            ->will($this->returnValue('vatID'));

        $this->customerVatMock->expects($this->once())
            ->method('isCountryInEU')
            ->with('customerCountryCode')
            ->willReturn(true);

        $this->quoteMock->expects($this->once())
            ->method('getCustomerGroupId')
            ->will($this->returnValue('customerGroupId'));

        $validationResult = ['some' => 'result'];
        $this->vatValidatorMock->expects($this->once())
            ->method('validate')
            ->with($this->quoteAddressMock, $this->storeId)
            ->will($this->returnValue($validationResult));

        $this->customerVatMock->expects($this->once())
            ->method('getCustomerGroupIdBasedOnVatNumber')
            ->with('customerCountryCode', $validationResult, $this->storeId)
            ->will($this->returnValue('customerGroupId'));

        /** Assertions */
        $this->quoteAddressMock->expects($this->once())
            ->method('setPrevQuoteCustomerGroupId')
            ->with('customerGroupId');

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

        $customerAddress = $this->createMock(Address::class);
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
            ->will($this->returnValue(true));

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
            ->will($this->returnValue(true));

        $this->quoteAddressMock->expects($this->once())
            ->method('getCountryId')
            ->will($this->returnValue(null));
        $this->quoteAddressMock->expects($this->once())
            ->method('getVatId')
            ->will($this->returnValue(null));

        $this->customerVatMock->expects($this->once())
            ->method('isCountryInEU')
            ->with($customerCountryCode)
            ->willReturn(true);

        $this->quoteMock->expects($this->once())
            ->method('getCustomerGroupId')
            ->will($this->returnValue('customerGroupId'));
        $validationResult = ['some' => 'result'];
        $this->customerVatMock->expects($this->once())
            ->method('getCustomerGroupIdBasedOnVatNumber')
            ->with($customerCountryCode, $validationResult, $this->storeId)
            ->will($this->returnValue('customerGroupId'));
        $this->customerSession->expects($this->once())
            ->method("setCustomerGroupId")
            ->with('customerGroupId');

        $this->vatValidatorMock->expects($this->once())
            ->method('validate')
            ->with($this->quoteAddressMock, $this->storeId)
            ->will($this->returnValue($validationResult));

        /** Assertions */
        $this->quoteAddressMock->expects($this->once())
            ->method('setPrevQuoteCustomerGroupId')
            ->with('customerGroupId');

        $this->quoteMock->expects($this->once())->method('setCustomerGroupId')->with('customerGroupId');
        $this->quoteMock->expects($this->once())->method('setCustomer')->with($this->customerMock);
        $this->customerDataFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->customerMock);
        $this->model->execute($this->observerMock);
    }
}
