<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\AdminOrder;

use Magento\Backend\Model\Session\Quote as SessionQuote;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Registry;
use Magento\Quote\Model\Quote;
use Magento\Sales\Api\Data\OrderAddressExtensionInterface;
use Magento\Sales\Api\Data\OrderAddressExtensionInterfaceFactory;
use Magento\Sales\Api\OrderManagementInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\AdminOrder\EmailSender;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\Response;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @magentoAppArea adminhtml
 * @magentoAppIsolation enabled
 */
class CreateTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Create
     */
    private $model;

    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * @var EmailSender|MockObject
     */
    private $emailSenderMock;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->messageManager = $this->objectManager->get(ManagerInterface::class);
        $this->emailSenderMock = $this->getMockBuilder(EmailSender::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->model =$this->objectManager->create(
            Create::class,
            ['messageManager' => $this->messageManager, 'emailSender' => $this->emailSenderMock]
        );
    }

    /**
     * @magentoDataFixture Magento/Downloadable/_files/product_downloadable.php
     * @magentoDataFixture Magento/Downloadable/_files/order_with_downloadable_product.php
     * @magentoDbIsolation disabled
     */
    public function testInitFromOrderShippingAddressSameAsBillingWhenEmpty()
    {
        /** @var $order Order */
        $order = $this->objectManager->create(Order::class);
        $order->loadByIncrementId('100000001');
        self::assertNull($order->getShippingAddress());

        $this->objectManager->get(Registry::class)->unregister('rule_data');
        $this->model->initFromOrder($order);

        self::assertNull($order->getShippingAddress());
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Downloadable/_files/product_downloadable.php
     * @magentoDataFixture Magento/Downloadable/_files/order_with_downloadable_product_with_additional_options.php
     * @magentoDbIsolation disabled
     * @magentoAppIsolation enabled
     */
    public function testInitFromOrderAndCreateOrderFromQuoteWithAdditionalOptions()
    {
        /** @var $serializer \Magento\Framework\Serialize\Serializer\Json */
        $serializer = $this->objectManager->create(\Magento\Framework\Serialize\Serializer\Json::class);

        /** @var $order Order */
        $order = $this->objectManager->create(Order::class);
        $order->loadByIncrementId('100000001');

        /** @var $orderCreate \Magento\Sales\Model\AdminOrder\Create */
        $order->setReordered(true);
        $orderCreate = $this->model->initFromOrder($order);

        $quoteItems = $orderCreate->getQuote()->getItemsCollection();

        self::assertEquals(1, $quoteItems->count());

        $quoteItem = $quoteItems->getFirstItem();
        $quoteItemOptions = $quoteItem->getOptionsByCode();

        self::assertEquals(
            $serializer->serialize(['additional_option_key' => 'additional_option_value']),
            $quoteItemOptions['additional_options']->getValue()
        );

        $session = $this->objectManager->get(SessionQuote::class);
        $session->setCustomerId(1);

        $customer = $this->objectManager->create(Customer::class);
        $customer->load(1)->setDefaultBilling(null)->setDefaultShipping(null)->save();
        $customerMock = $this->getMockedCustomer();

        $rate = $this->objectManager->create(Quote\Address\Rate::class);
        $rate->setCode('freeshipping_freeshipping');

        $this->model->getQuote()->setCustomer($customerMock);
        $this->model->getQuote()->getShippingAddress()->addShippingRate($rate);
        $this->model->getQuote()->getShippingAddress()->setCountryId('EE');
        $this->model->setShippingAsBilling(0);
        $this->model->setPaymentData(['method' => 'checkmo']);

        $newOrder = $this->model->createOrder();
        $newOrderItems = $newOrder->getItemsCollection();

        self::assertEquals(1, $newOrderItems->count());

        $order->loadByIncrementId('100000001');
        $this->assertEquals($newOrder->getRealOrderId(), $order->getRelationChildRealId());
        $this->assertEquals($newOrder->getId(), $order->getRelationChildId());

        $newOrderItem = $newOrderItems->getFirstItem();

        self::assertEquals(
            ['additional_option_key' => 'additional_option_value'],
            $newOrderItem->getProductOptionByCode('additional_options')
        );
        Response::closeOutputBuffers(1, false);
    }

    /**
     * @magentoDataFixture Magento/Downloadable/_files/product_downloadable.php
     * @magentoDataFixture Magento/Downloadable/_files/order_with_downloadable_product.php
     * @magentoDataFixture Magento/Sales/_files/order_shipping_address_same_as_billing.php
     * @magentoDbIsolation disabled
     */
    public function testInitFromOrderShippingAddressSameAsBillingWhenSame()
    {
        /** @var $order Order */
        $order = $this->objectManager->create(Order::class);
        $order->loadByIncrementId('100000001');

        self::assertNull($order->getShippingAddress()->getSameAsBilling());

        /** @var OrderAddressExtensionInterface $shippingExtAttributes */
        $shippingExtAttributes = $this->objectManager->get(OrderAddressExtensionInterfaceFactory::class)
            ->create();

        $billingExtAttributes = clone $shippingExtAttributes;

        $shippingExtAttributes->setData('tmp', false);
        $billingExtAttributes->setData('tmp', true);

        $order->getShippingAddress()->setExtensionAttributes($shippingExtAttributes);
        $order->getBillingAddress()->setExtensionAttributes($billingExtAttributes);

        $this->objectManager->get(Registry::class)->unregister('rule_data');
        $this->model->initFromOrder($order);

        self::assertTrue($order->getShippingAddress()->getSameAsBilling());
    }

    /**
     * @magentoDataFixture Magento/Downloadable/_files/product_downloadable.php
     * @magentoDataFixture Magento/Downloadable/_files/order_with_downloadable_product.php
     * @magentoDataFixture Magento/Sales/_files/order_shipping_address_different_to_billing.php
     * @magentoDbIsolation disabled
     */
    public function testInitFromOrderShippingAddressSameAsBillingWhenDifferent()
    {
        /** @var $order Order */
        $order = $this->objectManager->create(Order::class);
        $order->loadByIncrementId('100000002');

        self::assertNull($order->getShippingAddress()->getSameAsBilling());

        $this->objectManager->get(Registry::class)->unregister('rule_data');
        $this->model->initFromOrder($order);

        self::assertFalse($order->getShippingAddress()->getSameAsBilling());
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order_paid_with_payflowpro.php
     * @magentoDbIsolation disabled
     */
    public function testInitFromOrderCcInformationDeleted()
    {
        /** @var $order Order */
        $order = $this->objectManager->create(Order::class);
        $order->loadByIncrementId('100000001');

        $payment = $order->getPayment();
        self::assertEquals('5', $payment->getCcExpMonth());
        self::assertEquals('2016', $payment->getCcExpYear());
        self::assertEquals('AE', $payment->getCcType());
        self::assertEquals('0005', $payment->getCcLast4());

        $this->objectManager->get(Registry::class)->unregister('rule_data');
        $payment = $this->model->initFromOrder($order)->getQuote()->getPayment();

        self::assertNull($payment->getCcExpMonth());
        self::assertNull($payment->getCcExpYear());
        self::assertNull($payment->getCcType());
        self::assertNull($payment->getCcLast4());
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order.php
     * @magentoDbIsolation disabled
     */
    public function testInitFromOrderWithEmptyPaymentDetails()
    {
        /** @var $order Order */
        $order = $this->objectManager->create(Order::class);
        $order->loadByIncrementId('100000001');

        $this->objectManager->get(Registry::class)
            ->unregister('rule_data');

        $initOrder = $this->model->initFromOrder($order);
        $payment = $initOrder->getQuote()->getPayment();

        self::assertEquals($initOrder->getQuote()->getId(), $payment->getData('quote_id'));
        $payment->unsetData('quote_id');

        self::assertEmpty($payment->getMethod());
        self::assertEmpty($payment->getAdditionalInformation());
        self::assertEmpty($payment->getAdditionalData());
        self::assertEmpty($payment->getData());
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testGetCustomerWishlistNoCustomerId()
    {
        /** @var SessionQuote $session */
        $session = $this->objectManager->create(SessionQuote::class);
        $session->setCustomerId(null);
        self::assertFalse(
            $this->model->getCustomerWishlist(true),
            'If customer ID is not set to session, false is expected to be returned.'
        );
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoAppIsolation enabled
     * @magentoDbIsolation disabled
     */
    public function testGetCustomerWishlist()
    {
        $customerIdFromFixture = 1;
        $productIdFromFixture = 1;
        /** @var SessionQuote $session */
        $session = $this->objectManager->create(SessionQuote::class);
        $session->setCustomerId($customerIdFromFixture);

        /** Test new wishlist creation for the customer specified above */
        /** @var \Magento\Wishlist\Model\Wishlist $wishlist */
        $wishlist = $this->model->getCustomerWishlist(true);
        self::assertInstanceOf(
            \Magento\Wishlist\Model\Wishlist::class,
            $wishlist,
            'New Wish List is expected to be created if existing Customer does not have one yet.'
        );
        self::assertEquals(0, $wishlist->getItemsCount(), 'New Wish List must be empty just after creation.');

        /** Add new item to wishlist and try to get it using getCustomerWishlist once again */
        $wishlist->addNewItem($productIdFromFixture)->save();
        $updatedWishlist = $this->model->getCustomerWishlist(true);
        self::assertEquals(
            1,
            $updatedWishlist->getItemsCount(),
            'Wish List must contain a Product which was added to it earlier.'
        );

        /** Try to load wishlist from cache in the class after it is deleted from DB */
        $wishlist->delete();
        self::assertSame(
            $updatedWishlist,
            $this->model->getCustomerWishlist(false),
            'Wish List cached in class variable is expected to be returned.'
        );
        self::assertNotSame(
            $updatedWishlist,
            $this->model->getCustomerWishlist(true),
            'New Wish List is expected to be created when cache is forced to be refreshed.'
        );
    }

    /**
     * @magentoDbIsolation enabled
     */
    public function testSetBillingAddress()
    {
        $addressData = $this->getValidAddressData();
        /** Validate data before creating address object */
        $this->model->setIsValidate(true)->setBillingAddress($addressData);
        self::assertInstanceOf(
            Quote\Address::class,
            $this->model->getBillingAddress(),
            'Billing address object was not created.'
        );

        $expectedAddressData = array_merge(
            $addressData,
            [
                'address_type' => 'billing',
                'quote_id' => $this->model->getQuote()->getId(),
                'street' => "Line1\nLine2",
                'save_in_address_book' => 0,
                'region' => '',
                'region_id' => 1,
            ]
        );

        $result = $this->model->getBillingAddress()->getData();
        foreach ($expectedAddressData as $key => $value) {
            self::assertArrayHasKey($key, $result);
            self::assertEquals($value, $result[$key]);
        }
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDbIsolation disabled
     */
    public function testSetBillingAddressValidationErrors()
    {
        $customerIdFromFixture = 1;
        /** @var SessionQuote $session */
        $session = $this->objectManager->create(SessionQuote::class);
        $session->setCustomerId($customerIdFromFixture);
        $invalidAddressData = array_merge($this->getValidAddressData(), ['firstname' => '', 'lastname' => '']);
        /**
         * Note that validation errors are collected during setBillingAddress() call in the internal class variable,
         * but they are not set to message manager at this step.
         * They are set to message manager only during createOrder() call.
         */
        $this->model->setIsValidate(true)->setBillingAddress($invalidAddressData);
        try {
            $this->model->createOrder();
            $this->fail('Validation errors are expected to lead to exception during createOrder() call.');
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            /** createOrder is expected to throw exception with empty message when validation error occurs */
        }
        $errorMessages = [];
        /** @var $validationError \Magento\Framework\Message\Error */
        foreach ($this->messageManager->getMessages()->getItems() as $validationError) {
            $errorMessages[] = $validationError->getText();
        }
        self::assertTrue(
            in_array('Billing Address: "First Name" is a required value.', $errorMessages),
            'Expected validation message is absent.'
        );
        self::assertTrue(
            in_array('Billing Address: "Last Name" is a required value.', $errorMessages),
            'Expected validation message is absent.'
        );
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoAppIsolation enabled
     */
    public function testCreateOrderNewCustomerDifferentAddresses()
    {
        $productIdFromFixture = 1;
        $shippingMethod = 'freeshipping_freeshipping';
        $paymentMethod = 'checkmo';
        $shippingAddressAsBilling = 0;
        $customerEmail = 'new_customer@example.com';
        $firstNameForShippingAddress = 'FirstNameForShipping';
        $orderData = [
            'currency' => 'USD',
            'account' => ['group_id' => '1', 'email' => $customerEmail],
            'billing_address' => array_merge($this->getValidAddressData(), ['save_in_address_book' => '1']),
            'shipping_address' => array_merge(
                $this->getValidAddressData(),
                ['save_in_address_book' => '1', 'firstname' => $firstNameForShippingAddress]
            ),
            'shipping_method' => $shippingMethod,
            'comment' => ['customer_note' => ''],
            'send_confirmation' => true,
        ];
        $paymentData = ['method' => $paymentMethod];

        $this->preparePreconditionsForCreateOrder(
            $productIdFromFixture,
            $customerEmail,
            $shippingMethod,
            $shippingAddressAsBilling,
            $paymentData,
            $orderData,
            $paymentMethod
        );
        $order = $this->model->createOrder();
        $newBillAddress = $order->getBillingAddress();
        self::assertEquals($this->model->getQuote()->getCustomerFirstname(), $newBillAddress->getFirstname());
        self::assertEquals($this->model->getQuote()->getCustomerLastname(), $newBillAddress->getLastname());
        self::assertEquals($order->getCustomerFirstname(), $newBillAddress->getFirstname());
        self::assertEquals($order->getCustomerLastname(), $newBillAddress->getLastname());
        $this->verifyCreatedOrder($order, $shippingMethod);
        /** @var Customer $customer */
        $customer = $this->objectManager->create(Customer::class);
        $customer->load($order->getCustomerId());
        self::assertEquals(
            $firstNameForShippingAddress,
            $customer->getPrimaryShippingAddress()->getFirstname(),
            'Shipping address is saved incorrectly.'
        );
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_simple_with_decimal_qty.php
     * @magentoDbIsolation disabled
     * @magentoAppIsolation enabled
     */
    public function testCreateOrderNewCustomer()
    {
        $productIdFromFixture = 1;
        $shippingMethod = 'freeshipping_freeshipping';
        $paymentMethod = 'checkmo';
        $shippingAddressAsBilling = 1;
        $customerEmail = 'new_customer@example.com';
        $orderData = [
            'currency' => 'USD',
            'account' => ['group_id' => '1', 'email' => $customerEmail],
            'billing_address' => array_merge($this->getValidAddressData(), ['save_in_address_book' => '1']),
            'shipping_method' => $shippingMethod,
            'comment' => ['customer_note' => ''],
            'send_confirmation' => false,
        ];
        $paymentData = ['method' => $paymentMethod];

        $this->preparePreconditionsForCreateOrder(
            $productIdFromFixture,
            $customerEmail,
            $shippingMethod,
            $shippingAddressAsBilling,
            $paymentData,
            $orderData,
            $paymentMethod
        );
        $order = $this->model->createOrder();
        //Check, order considering decimal qty in product.
        foreach ($order->getItems() as $orderItem) {
            self::assertTrue($orderItem->getIsQtyDecimal());
        }
        $this->verifyCreatedOrder($order, $shippingMethod);
    }

    /**
     * Tests order creation with new customer after failed first place order action.
     *
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoDbIsolation disabled
     * @magentoAppIsolation enabled
     * @dataProvider createOrderNewCustomerWithFailedFirstPlaceOrderActionDataProvider
     * @param string $customerEmailFirstAttempt
     * @param string $customerEmailSecondAttempt
     */
    public function testCreateOrderNewCustomerWithFailedFirstPlaceOrderAction(
        $customerEmailFirstAttempt,
        $customerEmailSecondAttempt
    ) {
        $productIdFromFixture = 1;
        $shippingMethod = 'freeshipping_freeshipping';
        $paymentMethod = 'checkmo';
        $shippingAddressAsBilling = 1;
        $customerEmail = $customerEmailFirstAttempt;
        $orderData = [
            'currency' => 'USD',
            'account' => ['group_id' => '1', 'email' => $customerEmail],
            'billing_address' => array_merge($this->getValidAddressData(), ['save_in_address_book' => '1']),
            'shipping_method' => $shippingMethod,
            'comment' => ['customer_note' => ''],
            'send_confirmation' => false,
        ];
        $paymentData = ['method' => $paymentMethod];

        $this->preparePreconditionsForCreateOrder(
            $productIdFromFixture,
            $customerEmail,
            $shippingMethod,
            $shippingAddressAsBilling,
            $paymentData,
            $orderData,
            $paymentMethod
        );

        // Emulates failing place order action
        $orderManagement = $this->getMockForAbstractClass(OrderManagementInterface::class);
        $orderManagement->method('place')
            ->willThrowException(new \Exception('Can\'t place order'));
        $this->objectManager->addSharedInstance($orderManagement, OrderManagementInterface::class);
        try {
            $this->model->createOrder();
        } catch (\Exception $e) {
            $this->objectManager->removeSharedInstance(OrderManagementInterface::class);
        }

        $customerEmail = $customerEmailSecondAttempt ? :$this->model->getQuote()->getCustomer()->getEmail();
        $orderData['account']['email'] = $customerEmailSecondAttempt;

        $this->preparePreconditionsForCreateOrder(
            $productIdFromFixture,
            $customerEmail,
            $shippingMethod,
            $shippingAddressAsBilling,
            $paymentData,
            $orderData,
            $paymentMethod
        );

        $order = $this->model->createOrder();
        $this->verifyCreatedOrder($order, $shippingMethod);
    }

    /**
     * Email before and after failed first place order action.
     *
     * @case #1 Is the same.
     * @case #2 Changed after failed first place order action.
     * @return array
     */
    public function createOrderNewCustomerWithFailedFirstPlaceOrderActionDataProvider()
    {
        return [
            1 => ['customer@email.com', 'customer@email.com'],
            2 => ['customer@email.com', 'changed_customer@email.com'],
        ];
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDbIsolation disabled
     * @magentoAppIsolation enabled
     */
    public function testCreateOrderExistingCustomerDifferentAddresses()
    {
        $productIdFromFixture = 1;
        $customerIdFromFixture = 1;
        $customerEmailFromFixture = 'customer@example.com';
        $shippingMethod = 'freeshipping_freeshipping';
        $paymentMethod = 'checkmo';
        $shippingAddressAsBilling = 0;
        $firstNameForShippingAddress = 'FirstNameForShipping';
        $orderData = [
            'currency' => 'USD',
            'billing_address' => array_merge($this->getValidAddressData(), ['save_in_address_book' => '1']),
            'shipping_address' => array_merge(
                $this->getValidAddressData(),
                ['save_in_address_book' => '1', 'firstname' => $firstNameForShippingAddress]
            ),
            'shipping_method' => $shippingMethod,
            'comment' => ['customer_note' => ''],
            'send_confirmation' => false,
        ];
        $paymentData = ['method' => $paymentMethod];

        $this->preparePreconditionsForCreateOrder(
            $productIdFromFixture,
            $customerEmailFromFixture,
            $shippingMethod,
            $shippingAddressAsBilling,
            $paymentData,
            $orderData,
            $paymentMethod,
            $customerIdFromFixture
        );
        $customerMock = $this->getMockedCustomer();

        $this->model->getQuote()->setCustomer($customerMock);
        $order = $this->model->createOrder();
        $this->verifyCreatedOrder($order, $shippingMethod);
        $this->objectManager->get(CustomerRegistry::class)
            ->remove($order->getCustomerId());
        $customer = $this->objectManager->get(CustomerRepositoryInterface::class)
            ->getById($order->getCustomerId());
        $address = $this->objectManager->get(AddressRepositoryInterface::class)
            ->getById($customer->getDefaultShipping());
        self::assertEquals(
            $firstNameForShippingAddress,
            $address->getFirstname(),
            'Shipping address is saved incorrectly.'
        );
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDbIsolation disabled
     * @magentoAppIsolation enabled
     */
    public function testCreateOrderExistingCustomer()
    {
        $productIdFromFixture = 1;
        $customerIdFromFixture = 1;
        $customerEmailFromFixture = 'customer@example.com';
        $shippingMethod = 'freeshipping_freeshipping';
        $paymentMethod = 'checkmo';
        $shippingAddressAsBilling = 1;
        $orderData = [
            'currency' => 'USD',
            'billing_address' => array_merge($this->getValidAddressData(), ['save_in_address_book' => '1']),
            'shipping_method' => $shippingMethod,
            'comment' => ['customer_note' => ''],
            'send_confirmation' => false,
        ];
        $paymentData = ['method' => $paymentMethod];

        $this->preparePreconditionsForCreateOrder(
            $productIdFromFixture,
            $customerEmailFromFixture,
            $shippingMethod,
            $shippingAddressAsBilling,
            $paymentData,
            $orderData,
            $paymentMethod,
            $customerIdFromFixture
        );
        $customerMock = $this->getMockedCustomer();

        $this->model->getQuote()->setCustomer($customerMock);
        $order = $this->model->createOrder();
        if ($this->model->getSendConfirmation() && !$order->getEmailSent()) {
            $this->emailSenderMock->expects($this->once())
                ->method('send')
                ->willReturn(true);
        } else {
            $this->emailSenderMock->expects($this->never())->method('send');
        }
        $this->verifyCreatedOrder($order, $shippingMethod);
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/quote.php
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoAppIsolation enabled
     * @magentoDbIsolation disabled
     */
    public function testGetCustomerCartExistingCart()
    {
        $fixtureCustomerId = 1;

        /** Preconditions */
        /** @var SessionQuote $session */
        $session = $this->objectManager->create(SessionQuote::class);
        $session->setCustomerId($fixtureCustomerId);
        /** @var $quoteFixture Quote */
        $quoteFixture = $this->objectManager->create(Quote::class);
        $quoteFixture->load('test01', 'reserved_order_id');
        $quoteFixture->setCustomerIsGuest(false)->setCustomerId($fixtureCustomerId)->save();

        /** SUT execution */
        $customerQuote = $this->model->getCustomerCart();
        self::assertEquals($quoteFixture->getId(), $customerQuote->getId(), 'Quote ID is invalid.');

        /** Try to load quote once again to ensure that caching works correctly */
        $customerQuoteFromCache = $this->model->getCustomerCart();
        self::assertSame($customerQuote, $customerQuoteFromCache, 'Customer quote caching does not work correctly.');
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/quote.php
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoAppIsolation enabled
     * @magentoDbIsolation disabled
     */
    public function testMoveQuoteItemToCart()
    {
        $fixtureCustomerId = 1;

        /** Preconditions */
        /** @var SessionQuote $session */
        $session = $this->objectManager->create(SessionQuote::class);
        $session->setCustomerId($fixtureCustomerId);
        /** @var $quoteFixture Quote */
        $quoteFixture = $this->objectManager->create(Quote::class);
        $quoteFixture->load('test01', 'reserved_order_id');
        $quoteFixture->setCustomerIsGuest(false)->setCustomerId($fixtureCustomerId)->save();

        $customerQuote = $this->model->getCustomerCart();
        $item = $customerQuote->getAllVisibleItems()[0];

        $this->model->moveQuoteItem($item, 'cart', 3);
        self::assertEquals(4, $item->getQty(), 'Number of Qty isn\'t correct for Quote item.');
        self::assertEquals(3, $item->getQtyToAdd(), 'Number of added qty isn\'t correct for Quote item.');
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDbIsolation disabled
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testGetCustomerCartNewCart()
    {
        $customerIdFromFixture = 1;

        /** Preconditions */
        /** @var SessionQuote $session */
        $session = $this->objectManager->create(SessionQuote::class);
        $session->setCustomerId($customerIdFromFixture);

        /** SUT execution */
        $customerQuote = $this->model->getCustomerCart();
        self::assertInstanceOf(Quote::class, $customerQuote);
        self::assertEmpty($customerQuote->getData());
    }

    /**
     * Prepare preconditions for createOrder method invocation.
     *
     * @param int $productIdFromFixture
     * @param string $customerEmail
     * @param string $shippingMethod
     * @param int $shippingAddressAsBilling
     * @param array $paymentData
     * @param array $orderData
     * @param string $paymentMethod
     * @param int|null $customerIdFromFixture
     */
    private function preparePreconditionsForCreateOrder(
        $productIdFromFixture,
        $customerEmail,
        $shippingMethod,
        $shippingAddressAsBilling,
        $paymentData,
        $orderData,
        $paymentMethod,
        $customerIdFromFixture = null
    ) {
        /** Disable product options */
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $this->objectManager->create(\Magento\Catalog\Model\Product::class);
        $product->load($productIdFromFixture)->setHasOptions(false)->save();

        /** Set current customer */
        /** @var SessionQuote $session */
        $session = $this->objectManager->get(SessionQuote::class);
        if ($customerIdFromFixture !== null) {
            $session->setCustomerId($customerIdFromFixture);

            /** Unset fake IDs for default billing and shipping customer addresses */
            /** @var Customer $customer */
            $customer = $this->objectManager->create(Customer::class);
            if (empty($orderData['checkForDefaultStreet'])) {
                $customer->load($customerIdFromFixture)->setDefaultBilling(null)->setDefaultShipping(null)->save();
            }
        } else {
            /**
             * Customer ID must be set to session to pass \Magento\Sales\Model\AdminOrder\Create::_validate()
             * This code emulates order placement via admin panel.
             */
            $session->setCustomerId(0);
        }

        /** Emulate availability of shipping method (all are disabled by default) */
        /** @var $rate Quote\Address\Rate */
        $rate = $this->objectManager->create(Quote\Address\Rate::class);
        $rate->setCode($shippingMethod);
        $this->model->getQuote()->getShippingAddress()->addShippingRate($rate);

        $this->model->setShippingAsBilling($shippingAddressAsBilling);
        $this->model->addProduct($productIdFromFixture, ['qty' => 1]);
        $this->model->setPaymentData($paymentData);
        $this->model->setIsValidate(true)->importPostData($orderData);

        /** Check preconditions */

        self::assertEquals(
            0,
            $this->messageManager->getMessages()->getCount(),
            "Precondition failed: Errors occurred before SUT execution."
        );
        /** Selectively check quote data */
        $createOrderData = $this->model->getData();
        self::assertEquals(
            $shippingMethod,
            $createOrderData['shipping_method'],
            'Precondition failed: Shipping method specified in create order model is invalid'
        );
        self::assertEquals(
            'FirstName',
            $createOrderData['billing_address']['firstname'],
            'Precondition failed: Address data is invalid in create order model'
        );
        self::assertEquals(
            'Simple Product',
            $this->model->getQuote()->getItemByProduct($product)->getData('name'),
            'Precondition failed: Quote items data is invalid in create order model'
        );
        self::assertEquals(
            $customerEmail,
            $this->model->getQuote()->getCustomer()->getEmail(),
            'Precondition failed: Customer data is invalid in create order model'
        );
        self::assertEquals(
            $paymentMethod,
            $this->model->getQuote()->getPayment()->getData('method'),
            'Precondition failed: Payment method data is invalid in create order model'
        );
    }

    /**
     * Ensure that order is created correctly via createOrder().
     *
     * @param Order $order
     * @param string $shippingMethod
     */
    private function verifyCreatedOrder($order, $shippingMethod)
    {
        /** Selectively check order data */
        $orderData = $order->getData();
        self::assertNotEmpty($orderData['increment_id'], 'Order increment ID is empty.');
        self::assertEquals($this->model->getQuote()->getId(), $orderData['quote_id'], 'Quote ID is invalid.');
        self::assertEquals(
            $this->model->getQuote()->getCustomer()->getEmail(),
            $orderData['customer_email'],
            'Customer email is invalid.'
        );
        self::assertEquals(
            $this->model->getQuote()->getCustomer()->getFirstname(),
            $orderData['customer_firstname'],
            'Customer first name is invalid.'
        );
        self::assertEquals($shippingMethod, $orderData['shipping_method'], 'Shipping method is invalid.');
    }

    /**
     * Get valid address data for address creation.
     *
     * @return array
     */
    private function getValidAddressData()
    {
        return [
            'prefix' => 'prefix',
            'firstname' => 'FirstName',
            'middlename' => 'MiddleName',
            'lastname' => 'LastName',
            'suffix' => 'suffix',
            'company' => 'Company Name',
            'street' => [0 => 'Line1', 1 => 'Line2'],
            'city' => 'City',
            'country_id' => 'US',
            'region' => [
                'region' => '',
                'region_id' => '1',
            ],
            'postcode' => '76868',
            'telephone' => '+8709273498729384',
            'fax' => '',
            'vat_id' => ''
        ];
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Customer/_files/customer_address_attribute_update.php
     * @magentoDbIsolation disabled
     */
    public function testSetBillingAddressStreetValidationErrors()
    {
        $customerIdFromFixture = 1;
        /** @var SessionQuote $session */
        $session = $this->objectManager->create(SessionQuote::class);
        $session->setCustomerId($customerIdFromFixture);
        $invalidAddressData = array_merge($this->getValidAddressData(), ['street' => [0 => 'Whit`e', 1 => 'Lane']]);
        /**
         * Note that validation errors are collected during setBillingAddress() call in the internal class variable,
         * but they are not set to message manager at this step.
         * They are set to message manager only during createOrder() call.
         */
        $this->model->setIsValidate(true)->setBillingAddress($invalidAddressData);
        $this->model->setIsValidate(true)->setShippingAddress($invalidAddressData);
        try {
            $this->model->createOrder();
            $this->fail('Validation errors are expected to lead to exception during createOrder() call.');
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            /** createOrder is expected to throw exception with empty message when validation error occurs */
        }
        $errorMessages = [];
        /** @var $validationError \Magento\Framework\Message\Error */
        foreach ($this->messageManager->getMessages()->getItems() as $validationError) {
            $errorMessages[] = $validationError->getText();
        }
        self::assertTrue(
            in_array(
                'Billing Address: "Street Address" contains non-alphabetic or non-numeric characters.',
                $errorMessages
            ),
            'Expected validation message is absent.'
        );
        self::assertTrue(
            in_array(
                'Shipping Address: "Street Address" contains non-alphabetic or non-numeric characters.',
                $errorMessages
            ),
            'Expected validation message is absent.'
        );
    }

    /**
     * If the current street address for a customer differs with the default one (saved in customer address book)
     * then the updated validation rule (`input_validation`) should applied on current one while placing a new order.
     *
     * @magentoDataFixture Magento/Customer/_files/customer_address_street_attribute.php
     * @magentoDbIsolation disabled
     * @magentoAppIsolation enabled
     */
    public function testCreateOrderExistingCustomerWhenDefaultAddressDiffersWithNew()
    {
        $productIdFromFixture = 1;
        $customerIdFromFixture = 1;
        $customerEmailFromFixture = 'customer@example.com';
        $shippingMethod = 'freeshipping_freeshipping';
        $paymentMethod = 'checkmo';
        $shippingAddressAsBilling = 1;
        $invalidAddressData = array_merge($this->getValidAddressData(), ['street' => [0 => 'White', 1 => 'Lane']]);

        // Any change in default customer address should be treated as new address by setting up
        // `customer_address_id` to `null` in billing and shipping addresses.
        $address = array_merge($invalidAddressData, ['save_in_address_book' => '1', 'customer_address_id' => null]);
        $orderData = [
            'currency' => 'USD',
            'billing_address' => $address,
            'shipping_method' => $shippingMethod,
            'comment' => ['customer_note' => ''],
            'send_confirmation' => false,
            'checkForDefaultStreet' => true,
        ];
        $paymentData = ['method' => $paymentMethod];

        $this->preparePreconditionsForCreateOrder(
            $productIdFromFixture,
            $customerEmailFromFixture,
            $shippingMethod,
            $shippingAddressAsBilling,
            $paymentData,
            $orderData,
            $paymentMethod,
            $customerIdFromFixture
        );
        $this->model->setBillingAddress($orderData['billing_address']);
        try {
            $order =$this->model->createOrder();
            $orderData = $order->getData();
            self::assertNotEmpty($orderData['increment_id'], 'Order increment ID is empty.');
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            /** createOrder is expected to throw exception with empty message when validation error occurs */
            self::assertEquals('Validation is failed.', $e->getRawMessage());
        }
    }

    /**
     * Get customer mock object.
     *
     * @return \Magento\Customer\Model\Data\Customer
     */
    private function getMockedCustomer()
    {
        $customerMock = $this->getMockBuilder(\Magento\Customer\Model\Data\Customer::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'getId',
                    'getGroupId',
                    'getEmail',
                    '_getExtensionAttributes'
                ]
            )->getMock();
        $customerMock->method('getId')
            ->willReturn(1);
        $customerMock->method('getGroupId')
            ->willReturn(1);
        $customerMock->method('getEmail')
            ->willReturn('customer@example.com');
        $customerMock->method('_getExtensionAttributes')
            ->willReturn(null);

        return $customerMock;
    }
}
