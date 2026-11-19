<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Quote\Test\Unit\Observer\Frontend\Quote\Address;

use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Customer\Api\Data\GroupInterface;
use Magento\Customer\Api\GroupManagementInterface;
use Magento\Customer\Helper\Address as CustomerAddress;
use Magento\Customer\Model\Session;
use Magento\Customer\Model\Vat;
use Magento\Framework\Event\Observer;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Api\Data\ShippingInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Observer\Frontend\Quote\Address\CollectTotalsObserver;
use Magento\Quote\Observer\Frontend\Quote\Address\VatValidator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Rule\InvokedCount;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Class CollectTotalsTest
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CollectTotalsObserverTest extends TestCase
{
    use MockCreationTrait;

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
        $this->customerDataFactoryMock = $this->createPartialMockWithReflection(
            CustomerInterfaceFactory::class,
            ['create', 'mergeDataObjectWithArray']
        );
        $this->vatValidatorMock = $this->createMock(VatValidator::class);
        $this->observerMock = $this->createPartialMockWithReflection(
            Observer::class,
            ['setShippingAssignment', 'getShippingAssignment', 'setQuote', 'getQuote']
        );

        $this->quoteAddressMock = $this->createPartialMockWithReflection(
            Address::class,
            ['getCountryId', 'getVatId', 'getQuote', '__wakeup', 'setPrevQuoteCustomerGroupId']
        );

        $this->quoteMock = $this->createPartialMockWithReflection(
            Quote::class,
            ['getCustomerGroupId', 'getCustomer', '__wakeup', 'setCustomer', 'setCustomerGroupId']
        );

        $this->groupManagementMock = $this->createMock(GroupManagementInterface::class);

        $this->groupInterfaceMock = $this->createMock(GroupInterface::class);

        $shippingAssignmentMock = $this->createMock(ShippingAssignmentInterface::class);
        $shippingMock = $this->createMock(ShippingInterface::class);

        $shippingAssignmentMock->expects($this->once())->method('getShipping')->willReturn($shippingMock);
        $shippingMock->expects($this->once())->method('getAddress')->willReturn($this->quoteAddressMock);

        $this->observerMock->method('getShippingAssignment')->willReturn($shippingAssignmentMock);

        $this->observerMock->method('getQuote')->willReturn($this->quoteMock);
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
            $this->customerSession,
            $this->createMock(CustomerRepositoryInterface::class)
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
        $this->expectAddressWithoutVatId(2, null, $this->never());

        /** Assertions */
        $this->quoteMock->expects($this->once())->method('setCustomerGroupId')->with(2);
        $this->quoteMock->expects($this->once())->method('setCustomer')->with($this->customerMock);
        /** SUT execution */
        $this->model->execute($this->observerMock);
    }

    /**
     * @param int|null $accountGroupId null when the customer account no longer exists
     * @param int $expectedGroupId
     * @return void
     */
    #[DataProvider('vatAssignedQuoteGroupDataProvider')]
    public function testDispatchWithoutVatIdReplacesGroupAssignedByVatValidation(
        ?int $accountGroupId,
        int $expectedGroupId
    ): void {
        $this->expectAddressWithoutVatId(4, $accountGroupId, $this->once());

        /** Assertions */
        $this->quoteMock->expects($this->once())->method('setCustomerGroupId')->with($expectedGroupId);
        /** SUT execution */
        $this->model->execute($this->observerMock);
    }

    /**
     * Account group of a customer whose quote is in the intra-union group 4, and the expected group
     *
     * @return array
     */
    public static function vatAssignedQuoteGroupDataProvider(): array
    {
        return [
            'account in a group set by hand' => [2, 2],
            'account in the default group' => [1, 1],
            'account in the intra-union group' => [4, 1],
            'account no longer exists' => [null, 1],
        ];
    }

    /**
     * Address without a VAT ID; default group 1, VAT validation assigns groups 1 and 4
     *
     * @param int $quoteGroupId
     * @param int|null $accountGroupId null when the customer account no longer exists
     * @param InvokedCount $accountLoads
     * @return void
     */
    private function expectAddressWithoutVatId(
        int $quoteGroupId,
        ?int $accountGroupId,
        InvokedCount $accountLoads
    ): void {
        $this->vatValidatorMock->expects($this->once())
            ->method('isEnabled')
            ->with($this->quoteAddressMock, $this->storeId)
            ->willReturn(true);
        $this->quoteAddressMock->expects($this->once())
            ->method('getCountryId')
            ->willReturn('customerCountryCode');
        $this->quoteAddressMock->expects($this->once())->method('getVatId')->willReturn(null);
        $this->customerMock->method('getId')->willReturn('1');
        $this->quoteMock->method('getCustomerGroupId')->willReturn($quoteGroupId);
        $this->quoteAddressMock->expects($this->once())->method('setPrevQuoteCustomerGroupId')->with($quoteGroupId);
        $this->groupManagementMock->expects($this->once())
            ->method('getDefaultGroup')
            ->with($this->storeId)
            ->willReturn($this->groupInterfaceMock);
        $this->groupInterfaceMock->method('getId')->willReturn(1);

        $this->customerVatMock->expects($this->once())
            ->method('getVatValidationGroupIds')
            ->with($this->storeId)
            ->willReturn([1, 4]);
        $customerRepositoryMock = $this->createMock(CustomerRepositoryInterface::class);
        $loads = $customerRepositoryMock->expects($accountLoads)->method('getById')->with('1');
        if ($accountGroupId === null) {
            $loads->willThrowException(new NoSuchEntityException(__('No such entity.')));
        } else {
            $accountMock = $this->createMock(CustomerInterface::class);
            $accountMock->method('getGroupId')->willReturn($accountGroupId);
            $loads->willReturn($accountMock);
        }

        $this->model = new CollectTotalsObserver(
            $this->customerAddressMock,
            $this->customerVatMock,
            $this->vatValidatorMock,
            $this->customerDataFactoryMock,
            $this->groupManagementMock,
            $this->addressRepository,
            $this->customerSession,
            $customerRepositoryMock
        );
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
