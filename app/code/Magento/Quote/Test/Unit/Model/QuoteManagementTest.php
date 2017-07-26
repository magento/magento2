<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Quote\Test\Unit\Model;

use Magento\Framework\Exception\NoSuchEntityException;

use Magento\Quote\Model\CustomerManagement;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class QuoteManagementTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Quote\Model\QuoteManagement
     */
    protected $model;

    /**
     * @var \Magento\Quote\Model\QuoteValidator|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteValidator;

    /**
     * @var \Magento\Framework\Event\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $eventManager;

    /**
     * @var \Magento\Sales\Api\Data\OrderInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderFactory;

    /**
     * @var \Magento\Quote\Model\Quote\Address\ToOrder|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteAddressToOrder;

    /**
     * @var \Magento\Quote\Model\Quote\Payment\ToOrderPayment|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $quotePaymentToOrderPayment;

    /**
     * @var \Magento\Quote\Model\Quote\Address\ToOrderAddress|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteAddressToOrderAddress;

    /**
     * @var \Magento\Quote\Model\Quote\Item\ToOrderItem|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteItemToOrderItem;

    /**
     * @var \Magento\Quote\Model\Quote\Payment\ToOrderPayment|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderManagement;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteRepositoryMock;

    /**
     * @var CustomerManagement
     */
    protected $customerManagement;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $userContextMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerRepositoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteAddressFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $checkoutSessionMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerSessionMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $dataObjectHelperMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $accountManagementMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteIdMock;

    /**
     * @var \Magento\Customer\Api\AddressRepositoryInterface
     */
    private $addressRepositoryMock;

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->quoteValidator = $this->getMock(\Magento\Quote\Model\QuoteValidator::class, [], [], '', false);
        $this->eventManager = $this->getMockForAbstractClass(\Magento\Framework\Event\ManagerInterface::class);
        $this->orderFactory = $this->getMock(
            \Magento\Sales\Api\Data\OrderInterfaceFactory::class,
            [ 'create' ],
            [],
            '',
            false
        );
        $this->quoteAddressToOrder = $this->getMock(
            \Magento\Quote\Model\Quote\Address\ToOrder::class,
            [],
            [],
            '',
            false
        );
        $this->quotePaymentToOrderPayment = $this->getMock(
            \Magento\Quote\Model\Quote\Payment\ToOrderPayment::class,
            [],
            [],
            '',
            false
        );
        $this->quoteAddressToOrderAddress = $this->getMock(
            \Magento\Quote\Model\Quote\Address\ToOrderAddress::class,
            [],
            [],
            '',
            false
        );
        $this->quoteItemToOrderItem = $this->getMock(
            \Magento\Quote\Model\Quote\Item\ToOrderItem::class,
            [],
            [],
            '',
            false
        );
        $this->orderManagement = $this->getMock(\Magento\Sales\Api\OrderManagementInterface::class, [], [], '', false);
        $this->customerManagement = $this->getMock(\Magento\Quote\Model\CustomerManagement::class, [], [], '', false);
        $this->quoteRepositoryMock = $this->getMock(\Magento\Quote\Api\CartRepositoryInterface::class);

        $this->userContextMock = $this->getMock(
            \Magento\Authorization\Model\UserContextInterface::class,
            [],
            [],
            '',
            false
        );
        $this->customerRepositoryMock = $this->getMock(
            \Magento\Customer\Api\CustomerRepositoryInterface::class,
            ['create', 'save', 'get', 'getById', 'getList', 'delete', 'deleteById'],
            [],
            '',
            false
        );
        $this->customerFactoryMock = $this->getMock(
            \Magento\Customer\Model\CustomerFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $this->storeManagerMock = $this->getMockForAbstractClass(
            \Magento\Store\Model\StoreManagerInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['getStore', 'getStoreId']
        );

        $this->quoteMock = $this->getMock(
            \Magento\Quote\Model\Quote::class,
            [
                'getId',
                'getCheckoutMethod',
                'setCheckoutMethod',
                'setCustomerId',
                'setCustomerEmail',
                'getBillingAddress',
                'setCustomerIsGuest',
                'setCustomerGroupId',
                'assignCustomer',
                'getPayment',
            ],
            [],
            '',
            false
        );

        $this->quoteAddressFactory = $this->getMock(
            \Magento\Quote\Model\Quote\AddressFactory::class,
            ['create'],
            [],
            '',
            false
        );

        $this->dataObjectHelperMock = $this->getMock(\Magento\Framework\Api\DataObjectHelper::class, [], [], '', false);
        $this->checkoutSessionMock = $this->getMock(
            \Magento\Checkout\Model\Session::class,
            ['setLastQuoteId', 'setLastSuccessQuoteId', 'setLastOrderId', 'setLastRealOrderId', 'setLastOrderStatus'],
            [],
            '',
            false
        );
        $this->customerSessionMock = $this->getMock(\Magento\Customer\Model\Session::class, [], [], '', false);
        $this->accountManagementMock = $this->getMock(
            \Magento\Customer\Api\AccountManagementInterface::class,
            [],
            [],
            '',
            false
        );

        $this->quoteFactoryMock = $this->getMock(\Magento\Quote\Model\QuoteFactory::class, ['create'], [], '', false);
        $this->addressRepositoryMock = $this->getMockBuilder(\Magento\Customer\Api\AddressRepositoryInterface::class)
            ->getMockForAbstractClass();

        $this->model = $objectManager->getObject(
            \Magento\Quote\Model\QuoteManagement::class,
            [
                'eventManager' => $this->eventManager,
                'quoteValidator' => $this->quoteValidator,
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
        $this->quoteIdMock = $this->getMock(\Magento\Quote\Model\QuoteIdMask::class, [], [], '', false);
        $quoteIdFactoryMock = $this->getMock(\Magento\Quote\Model\QuoteIdMaskFactory::class, ['create'], [], '', false);
        $this->setPropertyValue($this->model, 'quoteIdMaskFactory', $quoteIdFactoryMock);
    }

    public function testCreateEmptyCartAnonymous()
    {
        $storeId = 345;
        $quoteId = 2311;

        $quoteMock = $this->getMock(\Magento\Quote\Model\Quote::class, [], [], '', false);

        $quoteAddress = $this->getMock(\Magento\Quote\Model\Quote\Address::class, [], [], '', false);

        $quoteMock->expects($this->any())->method('setBillingAddress')->with($quoteAddress)->willReturnSelf();
        $quoteMock->expects($this->any())->method('setShippingAddress')->with($quoteAddress)->willReturnSelf();

        $this->quoteAddressFactory->expects($this->any())->method('create')->willReturn($quoteAddress);

        $this->quoteFactoryMock->expects($this->once())->method('create')->willReturn($quoteMock);
        $quoteMock->expects($this->any())->method('setStoreId')->with($storeId);

        $this->quoteRepositoryMock->expects($this->once())->method('save')->with($quoteMock);
        $quoteMock->expects($this->once())->method('getId')->willReturn($quoteId);

        $this->storeManagerMock->expects($this->once())->method('getStore')->willReturnSelf();
        $this->storeManagerMock->expects($this->once())->method('getStoreId')->willReturn($storeId);

        $this->assertEquals($quoteId, $this->model->createEmptyCart());
    }

    public function testCreateEmptyCartForCustomer()
    {
        $storeId = 345;
        $quoteId = 2311;
        $userId = 567;

        $quoteMock = $this->getMock(\Magento\Quote\Model\Quote::class, [], [], '', false);

        $this->quoteRepositoryMock
            ->expects($this->once())
            ->method('getActiveForCustomer')
            ->with($userId)
            ->willThrowException(new NoSuchEntityException());

        $this->quoteFactoryMock->expects($this->once())->method('create')->willReturn($quoteMock);
        $quoteMock->expects($this->any())->method('setStoreId')->with($storeId);
        $quoteMock->expects($this->any())->method('setCustomerIsGuest')->with(0);

        $this->quoteRepositoryMock->expects($this->once())->method('save')->with($quoteMock);
        $quoteMock->expects($this->once())->method('getId')->willReturn($quoteId);

        $this->storeManagerMock->expects($this->once())->method('getStore')->willReturnSelf();
        $this->storeManagerMock->expects($this->once())->method('getStoreId')->willReturn($storeId);

        $this->assertEquals($quoteId, $this->model->createEmptyCartForCustomer($userId));
    }

    public function testCreateEmptyCartForCustomerReturnExistsQuote()
    {
        $storeId = 345;
        $userId = 567;

        $quoteMock = $this->getMock(\Magento\Quote\Model\Quote::class, [], [], '', false);

        $this->quoteRepositoryMock
            ->expects($this->once())
            ->method('getActiveForCustomer')
            ->with($userId)->willReturn($quoteMock);

        $this->quoteFactoryMock->expects($this->never())->method('create')->willReturn($quoteMock);
        $this->quoteRepositoryMock->expects($this->once())->method('save')->with($quoteMock);

        $this->storeManagerMock->expects($this->once())->method('getStore')->willReturnSelf();
        $this->storeManagerMock->expects($this->once())->method('getStoreId')->willReturn($storeId);

        $this->model->createEmptyCartForCustomer($userId);
    }

    /**
     * @expectedException \Magento\Framework\Exception\StateException
     * @expectedExceptionMessage Cannot assign customer to the given cart. The cart belongs to different store
     */
    public function testAssignCustomerFromAnotherStore()
    {
        $cartId = 220;
        $customerId = 455;
        $storeId = 5;

        $quoteMock = $this->getMock(\Magento\Quote\Model\Quote::class, [], [], '', false);
        $customerMock = $this->getMock(\Magento\Customer\Api\Data\CustomerInterface::class, [], [], '', false);

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

        $customerModelMock = $this->getMock(
            \Magento\Customer\Model\Customer::class,
            ['load', 'getSharedStoreIds'],
            [],
            '',
            false
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
     * @expectedException \Magento\Framework\Exception\StateException
     * @expectedExceptionMessage Cannot assign customer to the given cart. The cart is not anonymous.
     */
    public function testAssignCustomerToNonanonymousCart()
    {
        $cartId = 220;
        $customerId = 455;
        $storeId = 5;

        $quoteMock = $this->getMock(
            \Magento\Quote\Model\Quote::class,
            ['getCustomerId', 'setCustomer', 'setCustomerIsGuest'],
            [],
            '',
            false
        );
        $customerMock = $this->getMock(\Magento\Customer\Api\Data\CustomerInterface::class, [], [], '', false);

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

        $customerModelMock = $this->getMock(
            \Magento\Customer\Model\Customer::class,
            ['load', 'getSharedStoreIds'],
            [],
            '',
            false
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
     * @expectedException \Magento\Framework\Exception\StateException
     * @expectedExceptionMessage Cannot assign customer to the given cart. Customer already has active cart.
     */
    public function testAssignCustomerNoSuchCustomer()
    {
        $cartId = 220;
        $customerId = 455;
        $storeId = 5;

        $quoteMock = $this->getMock(
            \Magento\Quote\Model\Quote::class,
            ['getCustomerId', 'setCustomer', 'setCustomerIsGuest'],
            [],
            '',
            false
        );
        $customerMock = $this->getMock(\Magento\Customer\Api\Data\CustomerInterface::class, [], [], '', false);

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

        $customerModelMock = $this->getMock(
            \Magento\Customer\Model\Customer::class,
            ['load', 'getSharedStoreIds'],
            [],
            '',
            false
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
            ->with($customerId);

        $this->model->assignCustomer($cartId, $customerId, $storeId);
    }

    public function testAssignCustomer()
    {
        $cartId = 220;
        $customerId = 455;
        $storeId = 5;

        $this->getPropertyValue($this->model, 'quoteIdMaskFactory')
            ->expects($this->once())
            ->method('create')
            ->willReturn($this->quoteIdMock);
        $this->quoteIdMock->expects($this->once())->method('load')->with($cartId, 'quote_id')->willReturnSelf();
        $this->quoteIdMock->expects($this->once())->method('getId')->willReturn(10);
        $this->quoteIdMock->expects($this->once())->method('delete');
        $quoteMock = $this->getMock(
            \Magento\Quote\Model\Quote::class,
            ['getCustomerId', 'setCustomer', 'setCustomerIsGuest'],
            [],
            '',
            false
        );
        $customerMock = $this->getMock(\Magento\Customer\Api\Data\CustomerInterface::class, [], [], '', false);

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

        $customerModelMock = $this->getMock(
            \Magento\Customer\Model\Customer::class,
            ['load', 'getSharedStoreIds'],
            [],
            '',
            false
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

        $quoteMock->expects($this->once())->method('setCustomer')->with($customerMock);
        $quoteMock->expects($this->once())->method('setCustomerIsGuest')->with(0);

        $this->quoteRepositoryMock->expects($this->once())->method('save')->with($quoteMock);

        $this->model->assignCustomer($cartId, $customerId, $storeId);
    }

    public function testSubmit()
    {
        $orderData = [];
        $isGuest = true;
        $isVirtual = false;
        $customerId = 1;
        $quoteId = 1;
        $quoteItem = $this->getMock(\Magento\Quote\Model\Quote\Item::class, [], [], '', false);
        $billingAddress = $this->getMock(\Magento\Quote\Model\Quote\Address::class, [], [], '', false);
        $shippingAddress = $this->getMock(\Magento\Quote\Model\Quote\Address::class, [], [], '', false);
        $payment = $this->getMock(\Magento\Quote\Model\Quote\Payment::class, [], [], '', false);
        $baseOrder = $this->getMock(\Magento\Sales\Api\Data\OrderInterface::class, [], [], '', false);
        $convertedBillingAddress = $this->getMock(
            \Magento\Sales\Api\Data\OrderAddressInterface::class,
            [],
            [],
            '',
            false
        );
        $convertedShippingAddress = $this->getMock(
            \Magento\Sales\Api\Data\OrderAddressInterface::class,
            [],
            [],
            '',
            false
        );
        $convertedPayment = $this->getMock(\Magento\Sales\Api\Data\OrderPaymentInterface::class, [], [], '', false);
        $convertedQuoteItem = $this->getMock(\Magento\Sales\Api\Data\OrderItemInterface::class, [], [], '', false);

        $addresses = [$convertedShippingAddress, $convertedBillingAddress];
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

        $this->quoteValidator->expects($this->once())->method('validateBeforeSubmit')->with($quote);
        $this->quoteAddressToOrder->expects($this->once())
            ->method('convert')
            ->with($shippingAddress, $orderData)
            ->willReturn($baseOrder);
        $this->quoteAddressToOrderAddress->expects($this->at(0))
            ->method('convert')
            ->with(
                $shippingAddress,
                [
                    'address_type' => 'shipping',
                    'email' => 'customer@example.com'
                ]
            )
            ->willReturn($convertedShippingAddress);
        $this->quoteAddressToOrderAddress->expects($this->at(1))
            ->method('convert')
            ->with(
                $billingAddress,
                [
                    'address_type' => 'billing',
                    'email' => 'customer@example.com'
                ]
            )
            ->willReturn($convertedBillingAddress);
        $this->quoteItemToOrderItem->expects($this->once())->method('convert')
            ->with($quoteItem, ['parent_item' => null])
            ->willReturn($convertedQuoteItem);
        $this->quotePaymentToOrderPayment->expects($this->once())->method('convert')->with($payment)
            ->willReturn($convertedPayment);
        $shippingAddress->expects($this->once())->method('getShippingMethod')->willReturn('free');

        $order = $this->prepareOrderFactory(
            $baseOrder,
            $convertedBillingAddress,
            $addresses,
            $convertedPayment,
            $convertedItems,
            $quoteId,
            $convertedShippingAddress
        );

        $this->orderManagement->expects($this->once())
            ->method('place')
            ->with($order)
            ->willReturn($order);
        $this->eventManager->expects($this->at(0))
            ->method('dispatch')
            ->with('sales_model_service_quote_submit_before', ['order' => $order, 'quote' => $quote]);
        $this->eventManager->expects($this->at(1))
            ->method('dispatch')
            ->with('sales_model_service_quote_submit_success', ['order' => $order, 'quote' => $quote]);
        $this->quoteRepositoryMock->expects($this->once())->method('save')->with($quote);
        $this->assertEquals($order, $this->model->submit($quote, $orderData));
    }

    public function testPlaceOrderIfCustomerIsGuest()
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
            ->willReturn(\Magento\Checkout\Model\Type\Onepage::METHOD_GUEST);
        $this->quoteMock->expects($this->once())->method('setCustomerId')->with(null)->willReturnSelf();
        $this->quoteMock->expects($this->once())->method('setCustomerEmail')->with($email)->willReturnSelf();

        $addressMock = $this->getMock(\Magento\Quote\Model\Quote\Address::class, ['getEmail'], [], '', false);
        $addressMock->expects($this->once())->method('getEmail')->willReturn($email);
        $this->quoteMock->expects($this->once())->method('getBillingAddress')->with()->willReturn($addressMock);

        $this->quoteMock->expects($this->once())->method('setCustomerIsGuest')->with(true)->willReturnSelf();
        $this->quoteMock->expects($this->once())
            ->method('setCustomerGroupId')
            ->with(\Magento\Customer\Api\Data\GroupInterface::NOT_LOGGED_IN_ID);

        /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Quote\Model\QuoteManagement $service */
        $service = $this->getMock(
            \Magento\Quote\Model\QuoteManagement::class,
            ['submit'],
            [
                'eventManager' => $this->eventManager,
                'quoteValidator' => $this->quoteValidator,
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
                'quoteFactory' => $this->quoteFactoryMock
            ]
        );
        $orderMock = $this->getMock(
            \Magento\Sales\Model\Order::class,
            [],
            [],
            '',
            false
        );
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

        $this->assertEquals($orderId, $service->placeOrder($cartId));
    }

    public function testPlaceOrder()
    {
        $cartId = 323;
        $orderId = 332;
        $orderIncrementId = 100003332;
        $orderStatus = 'status1';

        /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Quote\Model\QuoteManagement $service */
        $service = $this->getMock(
            \Magento\Quote\Model\QuoteManagement::class,
            ['submit'],
            [
                'eventManager' => $this->eventManager,
                'quoteValidator' => $this->quoteValidator,
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
                'quoteFactory' => $this->quoteFactoryMock
            ]
        );
        $orderMock = $this->getMock(
            \Magento\Sales\Model\Order::class,
            [],
            [],
            '',
            false
        );

        $this->quoteRepositoryMock->expects($this->once())
            ->method('getActive')
            ->with($cartId)
            ->willReturn($this->quoteMock);

        $quotePayment = $this->getMock(\Magento\Quote\Model\Quote\Payment::class, [], [], '', false);
        $quotePayment->expects($this->once())
            ->method('setQuote');
        $quotePayment->expects($this->once())
            ->method('importData');
        $this->quoteMock->expects($this->atLeastOnce())
            ->method('getPayment')
            ->willReturn($quotePayment);

        $this->quoteMock->expects($this->once())
            ->method('getCheckoutMethod')
            ->willReturn(\Magento\Checkout\Model\Type\Onepage::METHOD_CUSTOMER);
        $this->quoteMock->expects($this->never())
            ->method('setCustomerIsGuest')
            ->with(true);

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

        $paymentMethod = $this->getMock(
            \Magento\Quote\Model\Quote\Payment::class,
            ['setChecks', 'getData'],
            [],
            '',
            false
        );
        $paymentMethod->expects($this->once())->method('setChecks');
        $paymentMethod->expects($this->once())->method('getData')->willReturn(['additional_data' => []]);

        $this->assertEquals($orderId, $service->placeOrder($cartId, $paymentMethod));
    }

    /**
     * @param $isGuest
     * @param $isVirtual
     * @param \Magento\Quote\Model\Quote\Address $billingAddress
     * @param \Magento\Quote\Model\Quote\Payment $payment
     * @param $customerId
     * @param $id
     * @param array $quoteItems
     * @param \Magento\Quote\Model\Quote\Address|null $shippingAddress
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getQuote(
        $isGuest,
        $isVirtual,
        \Magento\Quote\Model\Quote\Address $billingAddress,
        \Magento\Quote\Model\Quote\Payment $payment,
        $customerId,
        $id,
        array $quoteItems,
        \Magento\Quote\Model\Quote\Address $shippingAddress = null
    ) {
        $quote = $this->getMock(
            \Magento\Quote\Model\Quote::class,
            [
                'setIsActive',
                'getCustomerEmail',
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
                'getCustomerId',
                'addCustomerAddress'
            ],
            [],
            '',
            false
        );
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
        }
        $quote->expects($this->any())
            ->method('getBillingAddress')
            ->willReturn($billingAddress);
        $quote->expects($this->once())
            ->method('getPayment')
            ->willReturn($payment);

        $customer = $this->getMock(\Magento\Customer\Model\Customer::class, [], [], '', false);
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
     * @param \Magento\Sales\Api\Data\OrderInterface $baseOrder
     * @param \Magento\Sales\Api\Data\OrderAddressInterface $billingAddress
     * @param array $addresses
     * @param $payment
     * @param array $items
     * @param $quoteId
     * @param \Magento\Sales\Api\Data\OrderAddressInterface $shippingAddress
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function prepareOrderFactory(
        \Magento\Sales\Api\Data\OrderInterface $baseOrder,
        \Magento\Sales\Api\Data\OrderAddressInterface $billingAddress,
        array $addresses,
        $payment,
        array $items,
        $quoteId,
        \Magento\Sales\Api\Data\OrderAddressInterface $shippingAddress = null,
        $customerId = null
    ) {
        $order = $this->getMock(
            \Magento\Sales\Model\Order::class,
            ['setShippingAddress', 'getAddressesCollection', 'getAddresses', 'getBillingAddress', 'addAddresses',
                'setBillingAddress', 'setAddresses', 'setPayment', 'setItems', 'setQuoteId'],
            [],
            '',
            false
        );

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

    public function testGetCartForCustomer()
    {
        $customerId = 100;
        $cartMock = $this->getMock(\Magento\Quote\Model\Quote::class, [], [], '', false);
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
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testSubmitForCustomer()
    {
        $orderData = [];
        $isGuest = false;
        $isVirtual = false;
        $customerId = 1;
        $quoteId = 1;
        $quoteItem = $this->getMock(\Magento\Quote\Model\Quote\Item::class, [], [], '', false);
        $billingAddress = $this->getMock(\Magento\Quote\Model\Quote\Address::class, [], [], '', false);
        $shippingAddress = $this->getMock(\Magento\Quote\Model\Quote\Address::class, [], [], '', false);
        $payment = $this->getMock(\Magento\Quote\Model\Quote\Payment::class, [], [], '', false);
        $baseOrder = $this->getMock(\Magento\Sales\Api\Data\OrderInterface::class, [], [], '', false);
        $convertedBillingAddress = $this->getMock(
            \Magento\Sales\Api\Data\OrderAddressInterface::class,
            [],
            [],
            '',
            false
        );
        $convertedShippingAddress = $this->getMock(
            \Magento\Sales\Api\Data\OrderAddressInterface::class,
            [],
            [],
            '',
            false
        );
        $convertedPayment = $this->getMock(\Magento\Sales\Api\Data\OrderPaymentInterface::class, [], [], '', false);
        $convertedQuoteItem = $this->getMock(\Magento\Sales\Api\Data\OrderItemInterface::class, [], [], '', false);

        $addresses = [$convertedShippingAddress, $convertedBillingAddress];
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

        $this->quoteValidator->expects($this->once())->method('validateBeforeSubmit')->with($quote);
        $this->quoteAddressToOrder->expects($this->once())
            ->method('convert')
            ->with($shippingAddress, $orderData)
            ->willReturn($baseOrder);
        $this->quoteAddressToOrderAddress->expects($this->at(0))
            ->method('convert')
            ->with(
                $shippingAddress,
                [
                    'address_type' => 'shipping',
                    'email' => 'customer@example.com'
                ]
            )
            ->willReturn($convertedShippingAddress);
        $this->quoteAddressToOrderAddress->expects($this->at(1))
            ->method('convert')
            ->with(
                $billingAddress,
                [
                    'address_type' => 'billing',
                    'email' => 'customer@example.com'
                ]
            )
            ->willReturn($convertedBillingAddress);
        $this->quoteItemToOrderItem->expects($this->once())->method('convert')
            ->with($quoteItem, ['parent_item' => null])
            ->willReturn($convertedQuoteItem);
        $this->quotePaymentToOrderPayment->expects($this->once())->method('convert')->with($payment)
            ->willReturn($convertedPayment);
        $shippingAddress->expects($this->once())->method('getShippingMethod')->willReturn('free');

        $order = $this->prepareOrderFactory(
            $baseOrder,
            $convertedBillingAddress,
            $addresses,
            $convertedPayment,
            $convertedItems,
            $quoteId,
            $convertedShippingAddress
        );
        $customerAddressMock = $this->getMockBuilder(\Magento\Customer\Api\Data\AddressInterface::class)
            ->getMockForAbstractClass();
        $shippingAddress->expects($this->once())->method('exportCustomerAddress')->willReturn($customerAddressMock);
        $this->addressRepositoryMock->expects($this->once())->method('save')->with($customerAddressMock);
        $quote->expects($this->any())->method('addCustomerAddress')->with($customerAddressMock);
        $billingAddress->expects($this->once())->method('getCustomerId')->willReturn(2);
        $billingAddress->expects($this->once())->method('getSaveInAddressBook')->willReturn(false);
        $this->orderManagement->expects($this->once())
            ->method('place')
            ->with($order)
            ->willReturn($order);
        $this->eventManager->expects($this->at(0))
            ->method('dispatch')
            ->with('sales_model_service_quote_submit_before', ['order' => $order, 'quote' => $quote]);
        $this->eventManager->expects($this->at(1))
            ->method('dispatch')
            ->with('sales_model_service_quote_submit_success', ['order' => $order, 'quote' => $quote]);
        $this->quoteRepositoryMock->expects($this->once())->method('save')->with($quote);
        $this->assertEquals($order, $this->model->submit($quote, $orderData));
    }
}
