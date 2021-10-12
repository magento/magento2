<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Test\Unit\Model;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Checkout\Model\Session;
use Magento\Checkout\Model\Type\Onepage;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\Data\GroupInterface;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\CustomerManagement;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\Address\ToOrder;
use Magento\Quote\Model\Quote\Address\ToOrderAddress;
use Magento\Quote\Model\Quote\AddressFactory;
use Magento\Quote\Model\Quote\Item;
use Magento\Quote\Model\Quote\Item\ToOrderItem;
use Magento\Quote\Model\Quote\Payment;
use Magento\Quote\Model\Quote\Payment\ToOrderPayment;
use Magento\Quote\Model\QuoteFactory;
use Magento\Quote\Model\QuoteIdMask;
use Magento\Quote\Model\QuoteIdMaskFactory;
use Magento\Quote\Model\QuoteManagement;
use Magento\Quote\Model\SubmitQuoteValidator;
use Magento\Sales\Api\Data\OrderAddressInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderInterfaceFactory;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Sales\Api\OrderManagementInterface;
use Magento\Sales\Model\Order;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 */
class QuoteManagementTest extends TestCase
{
    /**
     * @var QuoteManagement
     */
    protected $model;

    /**
     * @var SubmitQuoteValidator|MockObject
     */
    protected $submitQuoteValidator;

    /**
     * @var ManagerInterface|MockObject
     */
    protected $eventManager;

    /**
     * @var OrderInterfaceFactory|MockObject
     */
    protected $orderFactory;

    /**
     * @var ToOrder|MockObject
     */
    protected $quoteAddressToOrder;

    /**
     * @var ToOrderPayment|MockObject
     */
    protected $quotePaymentToOrderPayment;

    /**
     * @var ToOrderAddress|MockObject
     */
    protected $quoteAddressToOrderAddress;

    /**
     * @var ToOrderItem|MockObject
     */
    protected $quoteItemToOrderItem;

    /**
     * @var ToOrderPayment|MockObject
     */
    protected $orderManagement;

    /**
     * @var CartRepositoryInterface|MockObject
     */
    protected $quoteRepositoryMock;

    /**
     * @var CustomerManagement
     */
    protected $customerManagement;

    /**
     * @var MockObject
     */
    protected $userContextMock;

    /**
     * @var MockObject
     */
    protected $customerRepositoryMock;

    /**
     * @var MockObject
     */
    protected $customerFactoryMock;

    /**
     * @var MockObject
     */
    protected $quoteAddressFactory;

    /**
     * @var MockObject
     */
    protected $storeManagerMock;

    /**
     * @var MockObject
     */
    protected $checkoutSessionMock;

    /**
     * @var MockObject
     */
    protected $customerSessionMock;

    /**
     * @var MockObject
     */
    protected $dataObjectHelperMock;

    /**
     * @var MockObject
     */
    protected $accountManagementMock;

    /**
     * @var MockObject
     */
    protected $quoteMock;

    /**
     * @var MockObject
     */
    private $quoteIdMock;

    /**
     * @var AddressRepositoryInterface
     */
    private $addressRepositoryMock;

    /**
     * @var MockObject
     */
    private $quoteFactoryMock;

    /**
     * @var RequestInterface|MockObject
     */
    private $requestMock;

    /**
     * @var RemoteAddress|MockObject
     */
    private $remoteAddressMock;

    /**
     * @var QuoteIdMaskFactory|MockObject
     */
    private $quoteIdMaskFactoryMock;

    /**
     * @inheriDoc
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->submitQuoteValidator = $this->createMock(SubmitQuoteValidator::class);
        $this->eventManager = $this->getMockForAbstractClass(ManagerInterface::class);
        $this->orderFactory = $this->getMockBuilder(OrderInterfaceFactory::class)
            ->addMethods(['populate'])
            ->onlyMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->quoteAddressToOrder = $this->createMock(ToOrder::class);
        $this->quotePaymentToOrderPayment = $this->createMock(ToOrderPayment::class);
        $this->quoteAddressToOrderAddress = $this->createMock(ToOrderAddress::class);
        $this->quoteItemToOrderItem = $this->createMock(ToOrderItem::class);
        $this->orderManagement = $this->getMockForAbstractClass(OrderManagementInterface::class);
        $this->customerManagement = $this->createMock(CustomerManagement::class);
        $this->quoteRepositoryMock = $this->getMockForAbstractClass(CartRepositoryInterface::class);

        $this->userContextMock = $this->getMockForAbstractClass(UserContextInterface::class);
        $this->customerRepositoryMock = $this->getMockBuilder(CustomerRepositoryInterface::class)
            ->addMethods(['create'])
            ->onlyMethods(['save', 'get', 'getById', 'getList', 'delete', 'deleteById'])
            ->getMockForAbstractClass();
        $this->customerFactoryMock = $this->createPartialMock(
            CustomerFactory::class,
            ['create']
        );
        $this->storeManagerMock = $this->getMockForAbstractClass(
            StoreManagerInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['getStore', 'getStoreId']
        );

        $this->quoteMock = $this->getMockBuilder(Quote::class)
            ->addMethods(['setCustomerEmail', 'setCustomerGroupId', 'setCustomerId', 'setRemoteIp', 'setXForwardedFor'])
            ->onlyMethods(
                [
                    'assignCustomer',
                    'collectTotals',
                    'getBillingAddress',
                    'getCheckoutMethod',
                    'getPayment',
                    'setCheckoutMethod',
                    'setCustomerIsGuest',
                    'getCustomer',
                    'getId'
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();

        $this->quoteAddressFactory = $this->createPartialMock(
            AddressFactory::class,
            ['create']
        );

        $this->dataObjectHelperMock = $this->createMock(DataObjectHelper::class);
        $this->checkoutSessionMock = $this->getMockBuilder(Session::class)
            ->addMethods(
                [
                    'setLastQuoteId',
                    'setLastSuccessQuoteId',
                    'setLastOrderId',
                    'setLastRealOrderId',
                    'setLastOrderStatus'
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();
        $this->customerSessionMock = $this->createMock(CustomerSession::class);
        $this->accountManagementMock = $this->getMockForAbstractClass(AccountManagementInterface::class);

        $this->quoteFactoryMock = $this->createPartialMock(QuoteFactory::class, ['create']);
        $this->addressRepositoryMock = $this->getMockBuilder(AddressRepositoryInterface::class)
            ->getMockForAbstractClass();

        $this->model = $objectManager->getObject(
            QuoteManagement::class,
            [
                'eventManager' => $this->eventManager,
                'submitQuoteValidator' => $this->submitQuoteValidator,
                'orderFactory' => $this->orderFactory,
                'orderManagement' => $this->orderManagement,
                'customerManagement' => $this->customerManagement,
                'quoteAddressToOrder' => $this->quoteAddressToOrder,
                'quoteAddressToOrderAddress' => $this->quoteAddressToOrderAddress,
                'quoteItemToOrderItem' => $this->quoteItemToOrderItem,
                'quotePaymentToOrderPayment' => $this->quotePaymentToOrderPayment,
                'userContext' => $this->userContextMock,
                'quoteRepository' => $this->quoteRepositoryMock,
                'customerRepository' => $this->customerRepositoryMock,
                'customerModelFactory' => $this->customerFactoryMock,
                'quoteAddressFactory' => $this->quoteAddressFactory,
                'dataObjectHelper' => $this->dataObjectHelperMock,
                'storeManager' => $this->storeManagerMock,
                'checkoutSession' => $this->checkoutSessionMock,
                'customerSession' => $this->customerSessionMock,
                'accountManagement' => $this->accountManagementMock,
                'quoteFactory' => $this->quoteFactoryMock,
                'addressRepository' => $this->addressRepositoryMock
            ]
        );

        // Set the new dependency
        $this->quoteIdMock = $this->createMock(QuoteIdMask::class);
        $this->quoteIdMaskFactoryMock = $this->createPartialMock(QuoteIdMaskFactory::class, ['create']);
        $this->setPropertyValue($this->model, 'quoteIdMaskFactory', $this->quoteIdMaskFactoryMock);

        $this->requestMock = $this->createPartialMockForAbstractClass(RequestInterface::class, ['getServer']);
        $this->remoteAddressMock = $this->createMock(RemoteAddress::class);
    }

    /**
     * @return void
     */
    public function testCreateEmptyCartAnonymous(): void
    {
        $storeId = 345;
        $quoteId = 2311;

        $quoteMock = $this->createMock(Quote::class);
        $quoteAddress = $this->getMockBuilder(Address::class)
            ->addMethods(['setCollectShippingRates'])
            ->disableOriginalConstructor()
            ->getMock();
        $quoteAddress->expects($this->once())->method('setCollectShippingRates')->with(true);

        $quoteMock->expects($this->any())->method('setBillingAddress')->with($quoteAddress)->willReturnSelf();
        $quoteMock->expects($this->any())->method('setShippingAddress')->with($quoteAddress)->willReturnSelf();
        $quoteMock->expects($this->any())->method('getShippingAddress')->willReturn($quoteAddress);

        $this->quoteAddressFactory->expects($this->any())->method('create')->willReturn($quoteAddress);

        $this->quoteFactoryMock->expects($this->once())->method('create')->willReturn($quoteMock);
        $quoteMock->expects($this->any())->method('setStoreId')->with($storeId);

        $this->quoteRepositoryMock->expects($this->once())->method('save')->with($quoteMock);
        $quoteMock->expects($this->once())->method('getId')->willReturn($quoteId);

        $this->storeManagerMock->expects($this->once())->method('getStore')->willReturnSelf();
        $this->storeManagerMock->expects($this->once())->method('getStoreId')->willReturn($storeId);

        $this->assertEquals($quoteId, $this->model->createEmptyCart());
    }

    /**
     * @return void
     */
    public function testCreateEmptyCartForCustomer(): void
    {
        $storeId = 345;
        $quoteId = 2311;
        $userId = 567;

        $quoteMock = $this->createMock(Quote::class);

        $this->quoteRepositoryMock
            ->expects($this->once())
            ->method('getActiveForCustomer')
            ->with($userId)
            ->willThrowException(new NoSuchEntityException());
        $customer = $this->getMockBuilder(CustomerInterface::class)
            ->onlyMethods(['getDefaultBilling'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $quoteAddress = $this->createPartialMock(
            Address::class,
            ['getCustomerId']
        );
        $quoteAddress->expects($this->atLeastOnce())->method('getCustomerId')->willReturn(567);
        $quoteMock->expects($this->atLeastOnce())->method('getBillingAddress')->willReturn($quoteAddress);

        $this->quoteFactoryMock->expects($this->once())->method('create')->willReturn($quoteMock);
        $quoteMock->expects($this->any())->method('setStoreId')->with($storeId);
        $quoteMock->expects($this->any())->method('setCustomerIsGuest')->with(0);

        $this->quoteRepositoryMock->expects($this->once())->method('save')->with($quoteMock);
        $quoteMock->expects($this->once())->method('getId')->willReturn($quoteId);
        $this->customerRepositoryMock->expects($this->atLeastOnce())->method('getById')->willReturn($customer);
        $customer->expects($this->atLeastOnce())->method('getDefaultBilling')->willReturn(0);

        $this->storeManagerMock->expects($this->once())->method('getStore')->willReturnSelf();
        $this->storeManagerMock->expects($this->once())->method('getStoreId')->willReturn($storeId);

        $this->assertEquals($quoteId, $this->model->createEmptyCartForCustomer($userId));
    }

    /**
     * @return void
     */
    public function testCreateEmptyCartForCustomerReturnExistsQuote(): void
    {
        $storeId = 345;
        $userId = 567;

        $quoteMock = $this->createMock(Quote::class);

        $this->quoteRepositoryMock
            ->expects($this->once())
            ->method('getActiveForCustomer')
            ->with($userId)->willReturn($quoteMock);

        $customer = $this->getMockBuilder(CustomerInterface::class)
            ->onlyMethods(['getDefaultBilling'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $quoteAddress = $this->createPartialMock(
            Address::class,
            ['getCustomerId']
        );
        $quoteAddress->expects($this->atLeastOnce())->method('getCustomerId')->willReturn(567);
        $quoteMock->expects($this->atLeastOnce())->method('getBillingAddress')->willReturn($quoteAddress);
        $this->customerRepositoryMock->expects($this->atLeastOnce())->method('getById')->willReturn($customer);
        $customer->expects($this->atLeastOnce())->method('getDefaultBilling')->willReturn(0);

        $this->quoteFactoryMock->expects($this->never())->method('create')->willReturn($quoteMock);
        $this->quoteRepositoryMock->expects($this->once())->method('save')->with($quoteMock);

        $this->storeManagerMock->expects($this->once())->method('getStore')->willReturnSelf();
        $this->storeManagerMock->expects($this->once())->method('getStoreId')->willReturn($storeId);

        $this->model->createEmptyCartForCustomer($userId);
    }

    /**
     * @return void
     */
    public function testAssignCustomerFromAnotherStore(): void
    {
        $this->expectException(StateException::class);
        $this->expectExceptionMessage(
            'The customer can\'t be assigned to the cart. The cart belongs to a different store.'
        );
        $cartId = 220;
        $customerId = 455;
        $storeId = 5;

        $quoteMock = $this->createMock(Quote::class);
        $customerMock = $this->getMockForAbstractClass(CustomerInterface::class);

        $this->quoteRepositoryMock
            ->expects($this->once())
            ->method('getActive')
            ->with($cartId)
            ->willReturn($quoteMock);

        $this->customerRepositoryMock
            ->expects($this->once())
            ->method('getById')
            ->with($customerId)
            ->willReturn($customerMock);

        $customerModelMock = $this->createPartialMock(
            Customer::class,
            ['load', 'getSharedStoreIds']
        );
        $this->customerFactoryMock->expects($this->once())->method('create')->willReturn($customerModelMock);
        $customerModelMock
            ->expects($this->once())
            ->method('load')
            ->with($customerId)
            ->willReturnSelf();

        $customerModelMock
            ->expects($this->once())
            ->method('getSharedStoreIds')
            ->willReturn([]);

        $this->model->assignCustomer($cartId, $customerId, $storeId);
    }

    /**
     * @return void
     */
    public function testAssignCustomerToNonanonymousCart(): void
    {
        $this->expectException(StateException::class);
        $this->expectExceptionMessage('The customer can\'t be assigned to the cart because the cart isn\'t anonymous.');
        $cartId = 220;
        $customerId = 455;
        $storeId = 5;

        $quoteMock = $this->getMockBuilder(Quote::class)
            ->addMethods(['getCustomerId'])
            ->onlyMethods(['setCustomer', 'setCustomerIsGuest'])
            ->disableOriginalConstructor()
            ->getMock();
        $customerMock = $this->getMockForAbstractClass(CustomerInterface::class);

        $this->quoteRepositoryMock
            ->expects($this->once())
            ->method('getActive')
            ->with($cartId)
            ->willReturn($quoteMock);

        $this->customerRepositoryMock
            ->expects($this->once())
            ->method('getById')
            ->with($customerId)
            ->willReturn($customerMock);

        $customerModelMock = $this->createPartialMock(
            Customer::class,
            ['load', 'getSharedStoreIds']
        );
        $this->customerFactoryMock->expects($this->once())->method('create')->willReturn($customerModelMock);
        $customerModelMock
            ->expects($this->once())
            ->method('load')
            ->with($customerId)
            ->willReturnSelf();

        $customerModelMock
            ->expects($this->once())
            ->method('getSharedStoreIds')
            ->willReturn([$storeId, 'some store value']);

        $quoteMock->expects($this->once())->method('getCustomerId')->willReturn(753);

        $this->model->assignCustomer($cartId, $customerId, $storeId);
    }

    /**
     * @return void
     */
    public function testAssignCustomerNoSuchCustomer(): void
    {
        $this->expectException(NoSuchEntityException::class);
        $cartId = 220;
        $customerId = 455;
        $storeId = 5;

        $quoteMock = $this->getMockBuilder(Quote::class)
            ->addMethods(['getCustomerId'])
            ->onlyMethods(['setCustomer', 'setCustomerIsGuest'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->quoteRepositoryMock
            ->expects($this->once())
            ->method('getActive')
            ->with($cartId)
            ->willReturn($quoteMock);

        $this->customerRepositoryMock
            ->expects($this->once())
            ->method('getById')
            ->with($customerId)
            ->willThrowException(new NoSuchEntityException());

        $this->expectExceptionMessage(
            "No such entity."
        );

        $this->model->assignCustomer($cartId, $customerId, $storeId);
    }

    /**
     * @return void
     */
    public function testAssignCustomerWithActiveCart(): void
    {
        $cartId = 220;
        $customerId = 455;
        $storeId = 5;

        $this->getPropertyValue($this->model, 'quoteIdMaskFactory')
            ->expects($this->once())
            ->method('create')
            ->willReturn($this->quoteIdMock);

        $quoteMock = $this->getMockBuilder(Quote::class)
            ->addMethods(['getCustomerId'])
            ->onlyMethods(['setCustomer', 'setCustomerIsGuest', 'setIsActive', 'getIsActive', 'merge'])
            ->disableOriginalConstructor()
            ->getMock();

        $activeQuoteMock = $this->getMockBuilder(Quote::class)
            ->addMethods(['getCustomerId'])
            ->onlyMethods(['setCustomer', 'setCustomerIsGuest', 'setIsActive', 'getIsActive', 'merge'])
            ->disableOriginalConstructor()
            ->getMock();

        $customerMock = $this->getMockForAbstractClass(CustomerInterface::class);

        $this->quoteRepositoryMock
            ->expects($this->once())
            ->method('getActive')
            ->with($cartId)
            ->willReturn($quoteMock);

        $this->customerRepositoryMock
            ->expects($this->once())
            ->method('getById')
            ->with($customerId)
            ->willReturn($customerMock);

        $customerModelMock = $this->createPartialMock(
            Customer::class,
            ['load', 'getSharedStoreIds']
        );
        $this->customerFactoryMock->expects($this->once())->method('create')->willReturn($customerModelMock);
        $customerModelMock
            ->expects($this->once())
            ->method('load')
            ->with($customerId)
            ->willReturnSelf();

        $customerModelMock
            ->expects($this->once())
            ->method('getSharedStoreIds')
            ->willReturn([$storeId, 'some store value']);

        $quoteMock->expects($this->once())->method('getCustomerId')->willReturn(null);
        $this->quoteRepositoryMock
            ->expects($this->once())
            ->method('getForCustomer')
            ->with($customerId)
            ->willReturn($activeQuoteMock);

        $quoteMock->expects($this->once())->method('merge')->with($activeQuoteMock)->willReturnSelf();
        $activeQuoteMock->expects($this->once())->method('setIsActive')->with(0);
        $this->quoteRepositoryMock->expects($this->atLeastOnce())->method('save')->with($activeQuoteMock);

        $quoteMock->expects($this->once())->method('setCustomer')->with($customerMock);
        $quoteMock->expects($this->once())->method('setCustomerIsGuest')->with(0);
        $quoteMock->expects($this->once())->method('setIsActive')->with(1);

        $this->quoteIdMock->expects($this->once())->method('load')->with($cartId, 'quote_id')->willReturnSelf();
        $this->quoteIdMock->expects($this->once())->method('getId')->willReturn(10);
        $this->quoteIdMock->expects($this->once())->method('delete');
        $this->quoteRepositoryMock->expects($this->atLeastOnce())->method('save')->with($quoteMock);

        $this->model->assignCustomer($cartId, $customerId, $storeId);
    }

    /**
     * @return void
     */
    public function testAssignCustomer(): void
    {
        $cartId = 220;
        $customerId = 455;
        $storeId = 5;

        $this->getPropertyValue($this->model, 'quoteIdMaskFactory')
            ->expects($this->once())
            ->method('create')
            ->willReturn($this->quoteIdMock);

        $quoteMock = $this->getMockBuilder(Quote::class)
            ->addMethods(['getCustomerId'])
            ->onlyMethods(['setCustomer', 'setCustomerIsGuest', 'setIsActive', 'getIsActive', 'merge'])
            ->disableOriginalConstructor()
            ->getMock();

        $customerMock = $this->getMockForAbstractClass(CustomerInterface::class);
        $this->quoteRepositoryMock
            ->expects($this->once())
            ->method('getActive')
            ->with($cartId)
            ->willReturn($quoteMock);

        $this->customerRepositoryMock
            ->expects($this->once())
            ->method('getById')
            ->with($customerId)
            ->willReturn($customerMock);

        $customerModelMock = $this->createPartialMock(
            Customer::class,
            ['load', 'getSharedStoreIds']
        );

        $this->customerFactoryMock->expects($this->once())->method('create')->willReturn($customerModelMock);

        $customerModelMock
            ->expects($this->once())
            ->method('load')
            ->with($customerId)
            ->willReturnSelf();

        $customerModelMock
            ->expects($this->once())
            ->method('getSharedStoreIds')
            ->willReturn([$storeId, 'some store value']);

        $quoteMock->expects($this->once())->method('getCustomerId')->willReturn(null);

        $this->quoteRepositoryMock
            ->expects($this->once())
            ->method('getForCustomer')
            ->with($customerId)
            ->willThrowException(new NoSuchEntityException());

        $quoteMock->expects($this->never())->method('merge');

        $quoteMock->expects($this->once())->method('setCustomer')->with($customerMock);
        $quoteMock->expects($this->once())->method('setCustomerIsGuest')->with(0);
        $quoteMock->expects($this->once())->method('setIsActive')->with(1);

        $this->quoteIdMock->expects($this->once())->method('load')->with($cartId, 'quote_id')->willReturnSelf();
        $this->quoteIdMock->expects($this->once())->method('getId')->willReturn(10);
        $this->quoteIdMock->expects($this->once())->method('delete');
        $this->quoteRepositoryMock->expects($this->once())->method('save')->with($quoteMock);

        $this->model->assignCustomer($cartId, $customerId, $storeId);
    }

    /**
     * @return void
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testSubmit(): void
    {
        $orderData = [];
        $isGuest = true;
        $isVirtual = false;
        $customerId = 1;
        $quoteId = 1;
        $quoteItem = $this->createMock(Item::class);
        $billingAddress = $this->createMock(Address::class);
        $shippingAddress = $this->getMockBuilder(Address::class)
            ->addMethods(['getQuoteId'])
            ->onlyMethods(['getShippingMethod', 'getId'])
            ->disableOriginalConstructor()
            ->getMock();
        $payment = $this->createMock(Payment::class);
        $baseOrder = $this->getMockForAbstractClass(OrderInterface::class);
        $convertedBilling = $this->createPartialMockForAbstractClass(OrderAddressInterface::class, ['setData']);
        $convertedShipping = $this->createPartialMockForAbstractClass(OrderAddressInterface::class, ['setData']);
        $convertedPayment = $this->getMockForAbstractClass(OrderPaymentInterface::class);
        $convertedQuoteItem = $this->getMockForAbstractClass(OrderItemInterface::class);
        $addresses = [$convertedShipping, $convertedBilling];
        $quoteItems = [$quoteItem];
        $convertedItems = [$convertedQuoteItem];
        $quote = $this->getQuote(
            $isGuest,
            $isVirtual,
            $billingAddress,
            $payment,
            $customerId,
            $quoteId,
            $quoteItems,
            $shippingAddress
        );

        $this->submitQuoteValidator->expects($this->once())
            ->method('validateQuote')
            ->with($quote);
        $this->quoteAddressToOrder->expects($this->once())
            ->method('convert')
            ->with($shippingAddress, $orderData)
            ->willReturn($baseOrder);
        $this->quoteAddressToOrderAddress
            ->method('convert')
            ->withConsecutive(
                [
                    $shippingAddress,
                    [
                        'address_type' => 'shipping',
                        'email' => 'customer@example.com'
                    ]
                ],
                [
                    $billingAddress,
                    [
                        'address_type' => 'billing',
                        'email' => 'customer@example.com'
                    ]
                ]
            )->willReturnOnConsecutiveCalls($convertedShipping, $convertedBilling);
        $billingAddress->expects($this->once())->method('getId')->willReturn(4);
        $convertedBilling->expects($this->once())->method('setData')->with('quote_address_id', 4);

        $this->quoteItemToOrderItem->expects($this->once())->method('convert')
            ->with($quoteItem, ['parent_item' => null])
            ->willReturn($convertedQuoteItem);
        $this->quotePaymentToOrderPayment->expects($this->once())->method('convert')->with($payment)
            ->willReturn($convertedPayment);
        $shippingAddress->expects($this->once())->method('getShippingMethod')->willReturn('free');
        $shippingAddress->expects($this->once())->method('getId')->willReturn(5);
        $convertedShipping->expects($this->once())->method('setData')->with('quote_address_id', 5);
        $order = $this->prepareOrderFactory(
            $baseOrder,
            $convertedBilling,
            $addresses,
            $convertedPayment,
            $convertedItems,
            $quoteId,
            $convertedShipping
        );

        $this->orderManagement->expects($this->once())
            ->method('place')
            ->with($order)
            ->willReturn($order);
        $this->eventManager
            ->method('dispatch')
            ->withConsecutive(
                [
                    'sales_model_service_quote_submit_before',
                    ['order' => $order, 'quote' => $quote]
                ],
                [
                    'sales_model_service_quote_submit_success',
                    ['order' => $order, 'quote' => $quote]
                ]
            );
        $this->quoteRepositoryMock->expects($this->once())->method('save')->with($quote);
        $this->assertEquals($order, $this->model->submit($quote, $orderData));
    }

    /**
     * @return void
     */
    public function testPlaceOrderIfCustomerIsGuest(): void
    {
        $cartId = 100;
        $orderId = 332;
        $orderIncrementId = 100003332;
        $orderStatus = 'status1';
        $email = 'email@mail.com';

        $this->quoteRepositoryMock->expects($this->once())
            ->method('getActive')
            ->with($cartId)
            ->willReturn($this->quoteMock);

        $this->quoteMock->expects($this->once())
            ->method('getCheckoutMethod')
            ->willReturn(Onepage::METHOD_GUEST);
        $customerMock = $this->getMockBuilder(Customer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->quoteMock->expects($this->once())
            ->method('getCustomer')
            ->willReturn($customerMock);
        $this->quoteMock->expects($this->once())->method('setCustomerId')->with(null)->willReturnSelf();
        $this->quoteMock->expects($this->once())->method('setCustomerEmail')->with($email)->willReturnSelf();

        $addressMock = $this->createPartialMock(Address::class, ['getEmail']);
        $addressMock->expects($this->once())->method('getEmail')->willReturn($email);
        $this->quoteMock->expects($this->any())->method('getBillingAddress')->with()->willReturn($addressMock);

        $this->quoteMock->expects($this->once())->method('setCustomerIsGuest')->with(true)->willReturnSelf();
        $this->quoteMock->expects($this->once())
            ->method('setCustomerGroupId')
            ->with(GroupInterface::NOT_LOGGED_IN_ID);

        /** @var MockObject|QuoteManagement $service */
        $service = $this->getMockBuilder(QuoteManagement::class)
            ->onlyMethods(['submit'])
            ->setConstructorArgs(
                [
                    'eventManager' => $this->eventManager,
                    'submitQuoteValidator' => $this->submitQuoteValidator,
                    'orderFactory' => $this->orderFactory,
                    'orderManagement' => $this->orderManagement,
                    'customerManagement' => $this->customerManagement,
                    'quoteAddressToOrder' => $this->quoteAddressToOrder,
                    'quoteAddressToOrderAddress' => $this->quoteAddressToOrderAddress,
                    'quoteItemToOrderItem' => $this->quoteItemToOrderItem,
                    'quotePaymentToOrderPayment' => $this->quotePaymentToOrderPayment,
                    'userContext' => $this->userContextMock,
                    'quoteRepository' => $this->quoteRepositoryMock,
                    'customerRepository' => $this->customerRepositoryMock,
                    'customerModelFactory' => $this->customerFactoryMock,
                    'quoteAddressFactory' => $this->quoteAddressFactory,
                    'dataObjectHelper' => $this->dataObjectHelperMock,
                    'storeManager' => $this->storeManagerMock,
                    'checkoutSession' => $this->checkoutSessionMock,
                    'customerSession' => $this->customerSessionMock,
                    'accountManagement' => $this->accountManagementMock,
                    'quoteFactory' => $this->quoteFactoryMock,
                    'quoteIdMaskFactory' => $this->quoteIdMaskFactoryMock,
                    'addressRepository' => $this->addressRepositoryMock,
                    'request' => $this->requestMock,
                    'remoteAddress' => $this->remoteAddressMock
                ]
            )
            ->getMock();

        $orderMock = $this->createPartialMock(
            Order::class,
            ['getId', 'getIncrementId', 'getStatus']
        );
        $service->expects($this->once())->method('submit')->willReturn($orderMock);

        $this->quoteMock->expects($this->atLeastOnce())->method('getId')->willReturn($cartId);
        $this->quoteMock->expects($this->once())->method('collectTotals')->willReturnSelf();

        $orderMock->expects($this->atLeastOnce())->method('getId')->willReturn($orderId);
        $orderMock->expects($this->atLeastOnce())->method('getIncrementId')->willReturn($orderIncrementId);
        $orderMock->expects($this->atLeastOnce())->method('getStatus')->willReturn($orderStatus);

        $this->checkoutSessionMock->expects($this->once())->method('setLastQuoteId')->with($cartId);
        $this->checkoutSessionMock->expects($this->once())->method('setLastSuccessQuoteId')->with($cartId);
        $this->checkoutSessionMock->expects($this->once())->method('setLastOrderId')->with($orderId);
        $this->checkoutSessionMock->expects($this->once())->method('setLastRealOrderId')->with($orderIncrementId);
        $this->checkoutSessionMock->expects($this->once())->method('setLastOrderStatus')->with($orderStatus);

        $this->assertEquals($orderId, $service->placeOrder($cartId));
    }

    /**
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testPlaceOrder(): void
    {
        $cartId = 323;
        $orderId = 332;
        $orderIncrementId = 100003332;
        $orderStatus = 'status1';
        $remoteAddress = '192.168.1.10';
        $forwardedForIp = '192.168.1.11';

        /** @var MockObject|QuoteManagement $service */
        $service = $this->getMockBuilder(QuoteManagement::class)
            ->onlyMethods(['submit'])
            ->setConstructorArgs(
                [
                    'eventManager' => $this->eventManager,
                    'submitQuoteValidator' => $this->submitQuoteValidator,
                    'orderFactory' => $this->orderFactory,
                    'orderManagement' => $this->orderManagement,
                    'customerManagement' => $this->customerManagement,
                    'quoteAddressToOrder' => $this->quoteAddressToOrder,
                    'quoteAddressToOrderAddress' => $this->quoteAddressToOrderAddress,
                    'quoteItemToOrderItem' => $this->quoteItemToOrderItem,
                    'quotePaymentToOrderPayment' => $this->quotePaymentToOrderPayment,
                    'userContext' => $this->userContextMock,
                    'quoteRepository' => $this->quoteRepositoryMock,
                    'customerRepository' => $this->customerRepositoryMock,
                    'customerModelFactory' => $this->customerFactoryMock,
                    'quoteAddressFactory' => $this->quoteAddressFactory,
                    'dataObjectHelper' => $this->dataObjectHelperMock,
                    'storeManager' => $this->storeManagerMock,
                    'checkoutSession' => $this->checkoutSessionMock,
                    'customerSession' => $this->customerSessionMock,
                    'accountManagement' => $this->accountManagementMock,
                    'quoteFactory' => $this->quoteFactoryMock,
                    'quoteIdMaskFactory' => $this->quoteIdMaskFactoryMock,
                    'addressRepository' => $this->addressRepositoryMock,
                    'request' => $this->requestMock,
                    'remoteAddress' => $this->remoteAddressMock
                ]
            )
            ->getMock();

        $orderMock = $this->createMock(Order::class);

        $this->quoteRepositoryMock->expects($this->once())
            ->method('getActive')
            ->with($cartId)
            ->willReturn($this->quoteMock);

        $quotePayment = $this->createMock(Payment::class);
        $quotePayment->expects($this->once())
            ->method('setQuote');
        $quotePayment->expects($this->once())
            ->method('importData');
        $this->quoteMock->expects($this->atLeastOnce())
            ->method('getPayment')
            ->willReturn($quotePayment);

        $this->quoteMock->expects($this->once())
            ->method('getCheckoutMethod')
            ->willReturn(Onepage::METHOD_CUSTOMER);

        $this->remoteAddressMock
            ->method('getRemoteAddress')
            ->willReturn($remoteAddress);

        $this->requestMock
            ->method('getServer')
            ->willReturn($forwardedForIp);

        $this->quoteMock->expects($this->once())->method('setRemoteIp')->with($remoteAddress);
        $this->quoteMock->expects($this->once())->method('setXForwardedFor')->with($forwardedForIp);

        $service->expects($this->once())->method('submit')->willReturn($orderMock);

        $this->quoteMock->expects($this->atLeastOnce())->method('getId')->willReturn($cartId);

        $orderMock->expects($this->atLeastOnce())->method('getId')->willReturn($orderId);
        $orderMock->expects($this->atLeastOnce())->method('getIncrementId')->willReturn($orderIncrementId);
        $orderMock->expects($this->atLeastOnce())->method('getStatus')->willReturn($orderStatus);

        $this->checkoutSessionMock->expects($this->once())->method('setLastQuoteId')->with($cartId);
        $this->checkoutSessionMock->expects($this->once())->method('setLastSuccessQuoteId')->with($cartId);
        $this->checkoutSessionMock->expects($this->once())->method('setLastOrderId')->with($orderId);
        $this->checkoutSessionMock->expects($this->once())->method('setLastRealOrderId')->with($orderIncrementId);
        $this->checkoutSessionMock->expects($this->once())->method('setLastOrderStatus')->with($orderStatus);

        $paymentMethod = $this->getMockBuilder(Payment::class)
            ->addMethods(['setChecks'])
            ->onlyMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMock();
        $paymentMethod->expects($this->once())->method('setChecks');
        $paymentMethod->expects($this->once())->method('getData')->willReturn(['additional_data' => []]);

        $this->assertEquals($orderId, $service->placeOrder($cartId, $paymentMethod));
    }

    /**
     * @param bool $isGuest
     * @param bool $isVirtual
     * @param Address $billingAddress
     * @param Payment $payment
     * @param int $customerId
     * @param int $id
     * @param array $quoteItems
     * @param Address|null $shippingAddress
     *
     * @return MockObject
     */
    protected function getQuote(
        bool $isGuest,
        bool $isVirtual,
        Address $billingAddress,
        Payment $payment,
        int $customerId,
        int $id,
        array $quoteItems,
        Address $shippingAddress = null
    ): MockObject {
        $quote = $this->getMockBuilder(Quote::class)
            ->addMethods(['getCustomerEmail', 'getCustomerId'])
            ->onlyMethods(
                [
                    'setIsActive',
                    'getAllVisibleItems',
                    'getCustomerIsGuest',
                    'isVirtual',
                    'getBillingAddress',
                    'getShippingAddress',
                    'getId',
                    'getCustomer',
                    'getAllItems',
                    'getPayment',
                    'reserveOrderId',
                    'addCustomerAddress'
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();
        $quote->expects($this->once())
            ->method('setIsActive')
            ->with(false);
        $quote->expects($this->any())
            ->method('getAllVisibleItems')
            ->willReturn($quoteItems);
        $quote->expects($this->once())
            ->method('getAllItems')
            ->willReturn($quoteItems);
        $quote->expects($this->once())
            ->method('getCustomerIsGuest')
            ->willReturn($isGuest);
        $quote->expects($this->any())
            ->method('isVirtual')
            ->willReturn($isVirtual);
        if ($shippingAddress) {
            $quote->expects($this->any())
                ->method('getShippingAddress')
                ->willReturn($shippingAddress);
            $shippingAddress->expects($this->any())->method('getQuoteId')->willReturn($id);
        }
        $quote->expects($this->any())
            ->method('getBillingAddress')
            ->willReturn($billingAddress);
        $quote->expects($this->once())
            ->method('getPayment')
            ->willReturn($payment);

        $customer = $this->getMockBuilder(Customer::class)
            ->addMethods(['getDefaultBilling'])
            ->onlyMethods(['getId'])
            ->disableOriginalConstructor()
            ->getMock();
        $quote->expects($this->any())->method('getCustomerId')->willReturn($customerId);

        $customer->expects($this->once())
            ->method('getId')
            ->willReturn($customerId);
        $quote->expects($this->atLeastOnce())
            ->method('getCustomerEmail')
            ->willReturn('customer@example.com');
        $quote->expects($this->any())
            ->method('getCustomer')
            ->willReturn($customer);
        $quote->expects($this->once())
            ->method('getId')
            ->willReturn($id);
        $this->customerRepositoryMock->expects($this->any())->method('getById')->willReturn($customer);

        $customer->expects($this->any())->method('getDefaultBilling')->willReturn(1);
        return $quote;
    }

    /**
     * @param OrderInterface $baseOrder
     * @param OrderAddressInterface $billingAddress
     * @param array $addresses
     * @param OrderPaymentInterface $payment
     * @param array $items
     * @param int $quoteId
     * @param OrderAddressInterface|null $shippingAddress
     * @param int|null $customerId
     *
     * @return MockObject
     */
    protected function prepareOrderFactory(
        OrderInterface $baseOrder,
        OrderAddressInterface $billingAddress,
        array $addresses,
        OrderPaymentInterface $payment,
        array $items,
        int $quoteId,
        OrderAddressInterface $shippingAddress = null,
        int $customerId = null
    ): MockObject {
        $order = $this->getMockBuilder(Order::class)
            ->addMethods(['addAddresses', 'setAddresses'])
            ->onlyMethods(
                [
                    'setShippingAddress',
                    'getAddressesCollection',
                    'getAddresses',
                    'getBillingAddress',
                    'setBillingAddress',
                    'setPayment',
                    'setItems',
                    'setQuoteId'
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();

        $this->orderFactory->expects($this->once())
            ->method('create')
            ->willReturn($order);
        $this->orderFactory->expects($this->never())
            ->method('populate')
            ->with($baseOrder);

        if ($shippingAddress) {
            $order->expects($this->once())->method('setShippingAddress')->with($shippingAddress);
        }
        if ($customerId) {
            $this->orderFactory->expects($this->once())
                ->method('setCustomerId')
                ->with($customerId);
        }
        $order->expects($this->any())->method('getAddressesCollection');
        $order->expects($this->any())->method('getAddresses');
        $order->expects($this->any())->method('getBillingAddress')->willReturn(false);
        $order->expects($this->any())->method('addAddresses')->withAnyParameters()->willReturnSelf();
        $order->expects($this->once())->method('setBillingAddress')->with($billingAddress);
        $order->expects($this->once())->method('setAddresses')->with($addresses);
        $order->expects($this->once())->method('setPayment')->with($payment);
        $order->expects($this->once())->method('setItems')->with($items);
        $order->expects($this->once())->method('setQuoteId')->with($quoteId);

        return $order;
    }

    /**
     * @return void
     * @throws NoSuchEntityException
     */
    public function testGetCartForCustomer(): void
    {
        $customerId = 100;
        $cartMock = $this->createMock(Quote::class);
        $this->quoteRepositoryMock->expects($this->once())
            ->method('getActiveForCustomer')
            ->with($customerId)
            ->willReturn($cartMock);
        $this->assertEquals($cartMock, $this->model->getCartForCustomer($customerId));
    }

    /**
     * Get any object property value.
     *
     * @param $object
     * @param $property
     * @return mixed
     */
    protected function getPropertyValue($object, $property)
    {
        $reflection = new \ReflectionClass(get_class($object));
        $reflectionProperty = $reflection->getProperty($property);
        $reflectionProperty->setAccessible(true);

        return $reflectionProperty->getValue($object);
    }

    /**
     * Set object property value.
     *
     * @param $object
     * @param $property
     * @param $value
     */
    protected function setPropertyValue(&$object, $property, $value)
    {
        $reflection = new \ReflectionClass(get_class($object));
        $reflectionProperty = $reflection->getProperty($property);
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($object, $value);

        return $object;
    }

    /**
     * Test submit for customer
     *
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testSubmitForCustomer(): void
    {
        $orderData = [];
        $isGuest = false;
        $isVirtual = false;
        $customerId = 1;
        $quoteId = 1;
        $quoteItem = $this->createMock(Item::class);
        $billingAddress = $this->createMock(Address::class);
        $shippingAddress = $this->getMockBuilder(Address::class)
            ->addMethods(['getQuoteId'])
            ->onlyMethods(['getShippingMethod', 'getId', 'exportCustomerAddress'])
            ->disableOriginalConstructor()
            ->getMock();
        $payment = $this->createMock(Payment::class);
        $baseOrder = $this->getMockForAbstractClass(OrderInterface::class);
        $convertedBilling = $this->createPartialMockForAbstractClass(OrderAddressInterface::class, ['setData']);
        $convertedShipping = $this->createPartialMockForAbstractClass(OrderAddressInterface::class, ['setData']);
        $convertedPayment = $this->getMockForAbstractClass(OrderPaymentInterface::class);
        $convertedQuoteItem = $this->getMockForAbstractClass(OrderItemInterface::class);

        $addresses = [$convertedShipping, $convertedBilling];
        $quoteItems = [$quoteItem];
        $convertedItems = [$convertedQuoteItem];

        $quote = $this->getQuote(
            $isGuest,
            $isVirtual,
            $billingAddress,
            $payment,
            $customerId,
            $quoteId,
            $quoteItems,
            $shippingAddress
        );

        $this->submitQuoteValidator->method('validateQuote')
            ->with($quote);
        $this->quoteAddressToOrder->expects($this->once())
            ->method('convert')
            ->with($shippingAddress, $orderData)
            ->willReturn($baseOrder);
        $this->quoteAddressToOrderAddress
            ->method('convert')
            ->withConsecutive(
                [
                    $shippingAddress,
                    [
                        'address_type' => 'shipping',
                        'email' => 'customer@example.com'
                    ]
                ],
                [
                    $billingAddress,
                    [
                        'address_type' => 'billing',
                        'email' => 'customer@example.com'
                    ]
                ]
            )
            ->willReturnOnConsecutiveCalls($convertedShipping, $convertedBilling);
        $this->quoteItemToOrderItem->expects($this->once())->method('convert')
            ->with($quoteItem, ['parent_item' => null])
            ->willReturn($convertedQuoteItem);
        $this->quotePaymentToOrderPayment->expects($this->once())->method('convert')->with($payment)
            ->willReturn($convertedPayment);
        $shippingAddress->expects($this->once())->method('getShippingMethod')->willReturn('free');
        $shippingAddress->expects($this->once())->method('getId')->willReturn(5);
        $convertedShipping->expects($this->once())->method('setData')->with('quote_address_id', 5);
        $order = $this->prepareOrderFactory(
            $baseOrder,
            $convertedBilling,
            $addresses,
            $convertedPayment,
            $convertedItems,
            $quoteId,
            $convertedShipping
        );
        $customerAddressMock = $this->getMockBuilder(AddressInterface::class)
            ->getMockForAbstractClass();
        $shippingAddress->expects($this->once())->method('exportCustomerAddress')->willReturn($customerAddressMock);
        $this->addressRepositoryMock->expects($this->once())->method('save')->with($customerAddressMock);
        $quote->expects($this->any())->method('addCustomerAddress')->with($customerAddressMock);
        $billingAddress->expects($this->once())->method('getCustomerId')->willReturn(2);
        $billingAddress->expects($this->once())->method('getSaveInAddressBook')->willReturn(false);
        $billingAddress->expects($this->once())->method('getId')->willReturn(4);
        $convertedBilling->expects($this->once())->method('setData')->with('quote_address_id', 4);
        $this->orderManagement->expects($this->once())
            ->method('place')
            ->with($order)
            ->willReturn($order);
        $this->eventManager
            ->method('dispatch')
            ->withConsecutive(
                [
                    'sales_model_service_quote_submit_before',
                    ['order' => $order, 'quote' => $quote]
                ],
                [
                    'sales_model_service_quote_submit_success',
                    ['order' => $order, 'quote' => $quote]
                ]
            );
        $this->quoteRepositoryMock->expects($this->once())->method('save')->with($quote);
        $this->assertEquals($order, $this->model->submit($quote, $orderData));
    }

    /**
     * Get mock for abstract class with methods.
     *
     * @param string $className
     * @param array $methods
     *
     * @return MockObject
     */
    private function createPartialMockForAbstractClass(string $className, array $methods = []): MockObject
    {
        return $this->getMockForAbstractClass(
            $className,
            [],
            '',
            true,
            true,
            true,
            $methods
        );
    }
}
