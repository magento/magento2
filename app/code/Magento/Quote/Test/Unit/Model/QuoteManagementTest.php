<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Quote\Test\Unit\Model;

use \Magento\Quote\Model\QuoteManagement;
use \Magento\Quote\Model\CustomerManagement;

use \Magento\Framework\Exception\NoSuchEntityException;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class QuoteManagementTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var QuoteManagement
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
     * @var \Magento\Quote\Model\QuoteRepository|\PHPUnit_Framework_MockObject_MockObject
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

    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->quoteValidator = $this->getMock('Magento\Quote\Model\QuoteValidator', [], [], '', false);
        $this->eventManager = $this->getMockForAbstractClass('Magento\Framework\Event\ManagerInterface');
        $this->orderFactory = $this->getMock(
            'Magento\Sales\Api\Data\OrderInterfaceFactory',
            [ 'create' ],
            [],
            '',
            false
        );
        $this->quoteAddressToOrder = $this->getMock(
            'Magento\Quote\Model\Quote\Address\ToOrder',
            [],
            [],
            '',
            false
        );
        $this->quotePaymentToOrderPayment = $this->getMock(
            'Magento\Quote\Model\Quote\Payment\ToOrderPayment',
            [],
            [],
            '',
            false
        );
        $this->quoteAddressToOrderAddress = $this->getMock(
            'Magento\Quote\Model\Quote\Address\ToOrderAddress',
            [],
            [],
            '',
            false
        );
        $this->quoteItemToOrderItem = $this->getMock('Magento\Quote\Model\Quote\Item\ToOrderItem', [], [], '', false);
        $this->orderManagement = $this->getMock('Magento\Sales\Api\OrderManagementInterface', [], [], '', false);
        $this->customerManagement = $this->getMock('Magento\Quote\Model\CustomerManagement', [], [], '', false);
        $this->quoteRepositoryMock = $this->getMock('\Magento\Quote\Model\QuoteRepository', [], [], '', false);

        $this->userContextMock = $this->getMock('\Magento\Authorization\Model\UserContextInterface', [], [], '', false);
        $this->customerRepositoryMock = $this->getMock(
            '\Magento\Customer\Api\CustomerRepositoryInterface',
            ['create', 'save', 'get', 'getById', 'getList', 'delete', 'deleteById'],
            [],
            '',
            false
        );
        $this->customerFactoryMock = $this->getMock(
            '\Magento\Customer\Model\CustomerFactory',
            ['create'],
            [],
            '',
            false
        );

        $dataObjectHelper = $this->getMock('\Magento\Framework\Api\DataObjectHelper', [], [], '', false);

        $this->model = $objectManager->getObject(
            'Magento\Quote\Model\QuoteManagement',
            [
                'quoteValidator' => $this->quoteValidator,
                'eventManager' => $this->eventManager,
                'orderFactory' => $this->orderFactory,
                'orderManagement' => $this->orderManagement,
                'customerManagement' => $this->customerManagement,
                'quoteAddressToOrder' => $this->quoteAddressToOrder,
                'quoteAddressToOrderAddress' => $this->quoteAddressToOrderAddress,
                'quoteItemToOrderItem' => $this->quoteItemToOrderItem,
                'quotePaymentToOrderPayment' => $this->quotePaymentToOrderPayment,
                'quoteRepository' => $this->quoteRepositoryMock,
                'userContext' => $this->userContextMock,
                'customerRepository' => $this->customerRepositoryMock,
                'customerModelFactory' => $this->customerFactoryMock,
                'dataObjectHelper' => $dataObjectHelper,
            ]
        );
    }

    public function testCreateEmptyCartAnonymous()
    {
        $storeId = 345;
        $quoteId = 2311;

        $quoteMock = $this->getMock('\Magento\Quote\Model\Quote', [], [], '', false);

        $this->userContextMock->expects($this->once())->method('getUserType')
            ->willReturn(\Magento\Authorization\Model\UserContextInterface::USER_TYPE_GUEST);

        $this->quoteRepositoryMock->expects($this->once())->method('create')->willReturn($quoteMock);
        $quoteMock->expects($this->any())->method('setStoreId')->with($storeId);


        $this->quoteRepositoryMock->expects($this->once())->method('save')->with($quoteMock);
        $quoteMock->expects($this->once())->method('getId')->willReturn($quoteId);

        $this->assertEquals($quoteId, $this->model->createEmptyCart($storeId));
    }

    public function testCreateEmptyCartLoggedInUser()
    {
        $storeId = 345;
        $quoteId = 2311;
        $userId = 567;

        $quoteMock = $this->getMock('\Magento\Quote\Model\Quote', [], [], '', false);

        $this->userContextMock->expects($this->once())->method('getUserType')
            ->willReturn(\Magento\Authorization\Model\UserContextInterface::USER_TYPE_CUSTOMER);

        $this->userContextMock->expects($this->atLeastOnce())->method('getUserId')->willReturn($userId);

        $customerMock = $this->getMock('\Magento\Customer\Api\Data\CustomerInterface', [], [], '', false);
        $this->customerRepositoryMock
            ->expects($this->once())
            ->method('getById')
            ->with($userId)
            ->willReturn($customerMock);

        $this->quoteRepositoryMock
            ->expects($this->once())
            ->method('getActiveForCustomer')
            ->with($userId)
            ->willThrowException(new NoSuchEntityException());

        $this->quoteRepositoryMock->expects($this->once())->method('create')->willReturn($quoteMock);
        $quoteMock->expects($this->any())->method('setStoreId')->with($storeId);
        $quoteMock->expects($this->any())->method('setCustomer')->with($customerMock);
        $quoteMock->expects($this->any())->method('setCustomerIsGuest')->with(0);


        $this->quoteRepositoryMock->expects($this->once())->method('save')->with($quoteMock);
        $quoteMock->expects($this->once())->method('getId')->willReturn($quoteId);

        $this->assertEquals($quoteId, $this->model->createEmptyCart($storeId));
    }

    /**
     * @expectedException \Magento\Framework\Exception\CouldNotSaveException
     */
    public function testCreateEmptyCartLoggedInUserException()
    {
        $storeId = 345;
        $userId = 567;

        $quoteMock = $this->getMock('\Magento\Quote\Model\Quote', [], [], '', false);

        $this->userContextMock->expects($this->once())->method('getUserType')
            ->willReturn(\Magento\Authorization\Model\UserContextInterface::USER_TYPE_CUSTOMER);

        $this->userContextMock->expects($this->atLeastOnce())->method('getUserId')->willReturn($userId);

        $customerMock = $this->getMock('\Magento\Customer\Api\Data\CustomerInterface', [], [], '', false);
        $this->customerRepositoryMock
            ->expects($this->once())
            ->method('getById')
            ->with($userId)
            ->willReturn($customerMock);

        $this->quoteRepositoryMock
            ->expects($this->once())
            ->method('getActiveForCustomer')
            ->with($userId);

        $this->quoteRepositoryMock->expects($this->never())->method('create')->willReturn($quoteMock);

        $this->quoteRepositoryMock->expects($this->never())->method('save')->with($quoteMock);

        $this->model->createEmptyCart($storeId);
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

        $quoteMock = $this->getMock('\Magento\Quote\Model\Quote', [], [], '', false);
        $customerMock = $this->getMock('\Magento\Customer\Api\Data\CustomerInterface', [], [], '', false);

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
            '\Magento\Customer\Model\Customer',
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
            '\Magento\Quote\Model\Quote',
            ['getCustomerId', 'setCustomer', 'setCustomerIsGuest'],
            [],
            '',
            false
        );
        $customerMock = $this->getMock('\Magento\Customer\Api\Data\CustomerInterface', [], [], '', false);

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
            '\Magento\Customer\Model\Customer',
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
            '\Magento\Quote\Model\Quote',
            ['getCustomerId', 'setCustomer', 'setCustomerIsGuest'],
            [],
            '',
            false
        );
        $customerMock = $this->getMock('\Magento\Customer\Api\Data\CustomerInterface', [], [], '', false);

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
            '\Magento\Customer\Model\Customer',
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

        $quoteMock = $this->getMock(
            '\Magento\Quote\Model\Quote',
            ['getCustomerId', 'setCustomer', 'setCustomerIsGuest'],
            [],
            '',
            false
        );
        $customerMock = $this->getMock('\Magento\Customer\Api\Data\CustomerInterface', [], [], '', false);

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
            '\Magento\Customer\Model\Customer',
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
        $quoteItem = $this->getMock('Magento\Quote\Model\Quote\Item', [], [], '', false);

        $billingAddress = $this->getMock('Magento\Quote\Model\Quote\Address', [], [], '', false);
        $shippingAddress = $this->getMock('Magento\Quote\Model\Quote\Address', [], [], '', false);
        $payment = $this->getMock('Magento\Quote\Model\Quote\Payment', [], [], '', false);

        $baseOrder = $this->getMock('Magento\Sales\Api\Data\OrderInterface', [], [], '', false);
        $convertedBillingAddress = $this->getMock('Magento\Sales\Api\Data\OrderAddressInterface', [], [], '', false);
        $convertedShippingAddress = $this->getMock('Magento\Sales\Api\Data\OrderAddressInterface', [], [], '', false);
        $convertedPayment = $this->getMock('Magento\Sales\Api\Data\OrderPaymentInterface', [], [], '', false);
        $convertedQuoteItem = $this->getMock('Magento\Sales\Api\Data\OrderItemInterface', [], [], '', false);

        $addresses = [$convertedShippingAddress, $convertedBillingAddress];
        $payments = [$convertedPayment];
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

        $this->quoteValidator->expects($this->once())
            ->method('validateBeforeSubmit')
            ->with($quote);

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
        $this->quoteItemToOrderItem->expects($this->once())
            ->method('convert')
            ->with($quoteItem, ['parent_item' => null])
            ->willReturn($convertedQuoteItem);
        $this->quotePaymentToOrderPayment->expects($this->once())
            ->method('convert')
            ->with($payment)
            ->willReturn($convertedPayment);

        $order = $this->prepareOrderFactory(
            $baseOrder,
            $convertedBillingAddress,
            $addresses,
            $payments,
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

    /**
     * //Last method throws exception because class method 'submit()' already covered.
     *
     * @expectedException \Exception
     * @expectedExceptionMessage Quote prepared for guest customer.
     */
    public function testPlaceOrderIfCustomerIsQuest()
    {
        $cartId = 100;
        $email = 'email@mail.com';
        $quoteMock = $this->getMock(
            'Magento\Quote\Model\Quote',
            [
                'getCheckoutMethod',
                'setCustomerId',
                'setCustomerEmail',
                'getBillingAddress',
                'setCustomerIsGuest',
                'setCustomerGroupId'
            ],
            [],
            '',
            false
        );
        $this->quoteRepositoryMock->expects($this->once())->method('getActive')->with($cartId)->willReturn($quoteMock);

        $quoteMock->expects($this->once())
            ->method('getCheckoutMethod')
            ->willReturn(\Magento\Checkout\Model\Type\Onepage::METHOD_GUEST);
        $quoteMock->expects($this->once())->method('setCustomerId')->with(null)->willReturnSelf();
        $quoteMock->expects($this->once())->method('setCustomerEmail')->with($email)->willReturnSelf();

        $addressMock = $this->getMock('\Magento\Quote\Model\Quote\Address', ['getEmail'], [], '', false);
        $addressMock->expects($this->once())->method('getEmail')->willReturn($email);
        $quoteMock->expects($this->once())->method('getBillingAddress')->with()->willReturn($addressMock);

        $quoteMock->expects($this->once())->method('setCustomerIsGuest')->with(true)->willReturnSelf();
        $quoteMock->expects($this->once())
            ->method('setCustomerGroupId')
            ->with(\Magento\Customer\Api\Data\GroupInterface::NOT_LOGGED_IN_ID)
            ->willThrowException(new \Exception('Quote prepared for guest customer.'));

        $this->model->placeOrder($cartId);
    }

    /**
     * @param $isGuest
     * @param $isVirtual
     * @param Quote\Address $billingAddress
     * @param Quote\Payment $payment
     * @param $customerId
     * @param $id
     * @param array $quoteItems
     * @param Quote\Address $shippingAddress
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
            'Magento\Quote\Model\Quote',
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
                'getPayment'
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
        $quote->expects($this->once())
            ->method('isVirtual')
            ->willReturn($isVirtual);
        if ($shippingAddress) {
            $quote->expects($this->exactly(2))
                ->method('getShippingAddress')
                ->willReturn($shippingAddress);
        }
        $quote->expects($this->once())
            ->method('getBillingAddress')
            ->willReturn($billingAddress);
        $quote->expects($this->once())
            ->method('getPayment')
            ->willReturn($payment);

        $customer = $this->getMock('Magento\Customer\Model\Customer', [], [], '', false);
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

        return $quote;
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderInterface $baseOrder
     * @param \Magento\Sales\Api\Data\OrderAddressInterface $billingAddress
     * @param array $addresses
     * @param array $payments
     * @param array $items
     * @param $quoteId
     * @param \Magento\Sales\Api\Data\OrderAddressInterface $shippingAddress
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function prepareOrderFactory(
        \Magento\Sales\Api\Data\OrderInterface $baseOrder,
        \Magento\Sales\Api\Data\OrderAddressInterface $billingAddress,
        array $addresses,
        array $payments,
        array $items,
        $quoteId,
        \Magento\Sales\Api\Data\OrderAddressInterface $shippingAddress = null,
        $customerId = null
    ) {
        $order = $this->getMock(
            'Magento\Sales\Model\Order',
            ['setShippingAddress', 'getAddressesCollection', 'getAddresses', 'getBillingAddress', 'addAddresses',
            'setBillingAddress', 'setAddresses', 'setPayments', 'setItems', 'setQuoteId'],
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
        $order->expects($this->once())->method('setPayments')->with($payments);
        $order->expects($this->once())->method('setItems')->with($items);
        $order->expects($this->once())->method('setQuoteId')->with($quoteId);

        return $order;
    }

    public function testGetCartForCustomer()
    {
        $customerId = 100;
        $cartMock = $this->getMock('\Magento\Quote\Model\Quote', [], [], '', false);
        $this->quoteRepositoryMock->expects($this->once())
            ->method('getActiveForCustomer')
            ->with($customerId)
            ->willReturn($cartMock);
        $this->assertEquals($cartMock, $this->model->getCartForCustomer($customerId));
    }
}
