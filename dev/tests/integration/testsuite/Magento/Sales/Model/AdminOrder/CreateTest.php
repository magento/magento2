<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\AdminOrder;

use Magento\TestFramework\Helper\Bootstrap;
use Magento\Sales\Model\Order;
use Magento\Framework\Registry;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @magentoAppArea adminhtml
 */
class CreateTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Sales\Model\AdminOrder\Create
     */
    protected $_model;

    /** @var \Magento\Framework\Message\ManagerInterface */
    protected $_messageManager;

    protected function setUp()
    {
        parent::setUp();
        $this->_messageManager = Bootstrap::getObjectManager()->get(\Magento\Framework\Message\ManagerInterface::class);
        $this->_model = Bootstrap::getObjectManager()->create(
            \Magento\Sales\Model\AdminOrder\Create::class,
            ['messageManager' => $this->_messageManager]
        );
    }

    /**
     * @magentoDataFixture Magento/Downloadable/_files/product_downloadable.php
     * @magentoDataFixture Magento/Downloadable/_files/order_with_downloadable_product.php
     */
    public function testInitFromOrderShippingAddressSameAsBillingWhenEmpty()
    {
        /** @var $order \Magento\Sales\Model\Order */
        $order = Bootstrap::getObjectManager()->create(\Magento\Sales\Model\Order::class);
        $order->loadByIncrementId('100000001');
        $this->assertNull($order->getShippingAddress());

        /** @var $objectManager \Magento\TestFramework\ObjectManager */
        $objectManager = Bootstrap::getObjectManager();
        $objectManager->get(\Magento\Framework\Registry::class)->unregister('rule_data');
        $this->_model->initFromOrder($order);

        $this->assertNull($order->getShippingAddress());
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Downloadable/_files/product_downloadable.php
     * @magentoDataFixture Magento/Downloadable/_files/order_with_downloadable_product_with_additional_options.php
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     */
    public function testInitFromOrderAndCreateOrderFromQuoteWithAdditionalOptions()
    {
        /** @var $serializer \Magento\Framework\Serialize\Serializer\Json */
        $serializer = Bootstrap::getObjectManager()->create(\Magento\Framework\Serialize\Serializer\Json::class);

        /** @var $order \Magento\Sales\Model\Order */
        $order = Bootstrap::getObjectManager()->create(\Magento\Sales\Model\Order::class);
        $order->loadByIncrementId('100000001');

        /** @var $orderCreate \Magento\Sales\Model\AdminOrder\Create */
        $orderCreate = $this->_model->initFromOrder($order);

        $quoteItems = $orderCreate->getQuote()->getItemsCollection();

        $this->assertEquals(1, $quoteItems->count());

        $quoteItem = $quoteItems->getFirstItem();
        $quoteItemOptions = $quoteItem->getOptionsByCode();

        $this->assertEquals(
            $serializer->serialize(['additional_option_key' => 'additional_option_value']),
            $quoteItemOptions['additional_options']->getValue()
        );

        $session = Bootstrap::getObjectManager()->get(\Magento\Backend\Model\Session\Quote::class);
        $session->setCustomerId(1);

        $customer = Bootstrap::getObjectManager()->create(\Magento\Customer\Model\Customer::class);
        $customer->load(1)->setDefaultBilling(null)->setDefaultShipping(null)->save();

        $rate = Bootstrap::getObjectManager()->create(\Magento\Quote\Model\Quote\Address\Rate::class);
        $rate->setCode('freeshipping_freeshipping');

        $this->_model->getQuote()->getShippingAddress()->addShippingRate($rate);
        $this->_model->setShippingAsBilling(0);
        $this->_model->setPaymentData(['method' => 'checkmo']);

        $newOrder = $this->_model->createOrder();
        $newOrderItems = $newOrder->getItemsCollection();

        $this->assertEquals(1, $newOrderItems->count());

        $newOrderItem = $newOrderItems->getFirstItem();

        $this->assertEquals(
            ['additional_option_key' => 'additional_option_value'],
            $newOrderItem->getProductOptionByCode('additional_options')
        );
    }

    /**
     * @magentoDataFixture Magento/Downloadable/_files/product_downloadable.php
     * @magentoDataFixture Magento/Downloadable/_files/order_with_downloadable_product.php
     * @magentoDataFixture Magento/Sales/_files/order_shipping_address_same_as_billing.php
     */
    public function testInitFromOrderShippingAddressSameAsBillingWhenSame()
    {
        /** @var $order \Magento\Sales\Model\Order */
        $order = Bootstrap::getObjectManager()->create(\Magento\Sales\Model\Order::class);
        $order->loadByIncrementId('100000001');

        $this->assertNull($order->getShippingAddress()->getSameAsBilling());

        /** @var $objectManager \Magento\TestFramework\ObjectManager */
        $objectManager = Bootstrap::getObjectManager();
        $objectManager->get(\Magento\Framework\Registry::class)->unregister('rule_data');
        $this->_model->initFromOrder($order);

        $this->assertTrue($order->getShippingAddress()->getSameAsBilling());
    }

    /**
     * @magentoDataFixture Magento/Downloadable/_files/product_downloadable.php
     * @magentoDataFixture Magento/Downloadable/_files/order_with_downloadable_product.php
     * @magentoDataFixture Magento/Sales/_files/order_shipping_address_different_to_billing.php
     */
    public function testInitFromOrderShippingAddressSameAsBillingWhenDifferent()
    {
        /** @var $objectManager \Magento\TestFramework\ObjectManager */
        $objectManager = Bootstrap::getObjectManager();

        /** @var $order \Magento\Sales\Model\Order */
        $order = $objectManager->create(\Magento\Sales\Model\Order::class);
        $order->loadByIncrementId('100000002');

        $this->assertNull($order->getShippingAddress()->getSameAsBilling());

        $objectManager->get(\Magento\Framework\Registry::class)->unregister('rule_data');
        $this->_model->initFromOrder($order);

        $this->assertFalse($order->getShippingAddress()->getSameAsBilling());
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order_paid_with_payflowpro.php
     */
    public function testInitFromOrderCcInformationDeleted()
    {
        /** @var $objectManager \Magento\TestFramework\ObjectManager */
        $objectManager = Bootstrap::getObjectManager();

        /** @var $order \Magento\Sales\Model\Order */
        $order = $objectManager->create(\Magento\Sales\Model\Order::class);
        $order->loadByIncrementId('100000001');

        $payment = $order->getPayment();
        $this->assertEquals('5', $payment->getCcExpMonth());
        $this->assertEquals('2016', $payment->getCcExpYear());
        $this->assertEquals('AE', $payment->getCcType());
        $this->assertEquals('0005', $payment->getCcLast4());

        $objectManager->get(\Magento\Framework\Registry::class)->unregister('rule_data');
        $payment = $this->_model->initFromOrder($order)->getQuote()->getPayment();

        $this->assertNull($payment->getCcExpMonth());
        $this->assertNull($payment->getCcExpYear());
        $this->assertNull($payment->getCcType());
        $this->assertNull($payment->getCcLast4());
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order.php
     */
    public function testInitFromOrderWithEmptyPaymentDetails()
    {
        /** @var $objectManager \Magento\TestFramework\ObjectManager */
        $objectManager = Bootstrap::getObjectManager();
        /** @var $order \Magento\Sales\Model\Order */
        $order = $objectManager->create(Order::class);
        $order->loadByIncrementId('100000001');

        $objectManager->get(Registry::class)
            ->unregister('rule_data');

        $initOrder = $this->_model->initFromOrder($order);
        $payment = $initOrder->getQuote()->getPayment();

        static::assertEquals($initOrder->getQuote()->getId(), $payment->getData('quote_id'));
        $payment->unsetData('quote_id');

        static::assertEmpty($payment->getMethod());
        static::assertEmpty($payment->getAdditionalInformation());
        static::assertEmpty($payment->getAdditionalData());
        static::assertEmpty($payment->getData());
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testGetCustomerWishlistNoCustomerId()
    {
        /** @var \Magento\Backend\Model\Session\Quote $session */
        $session = Bootstrap::getObjectManager()->create(\Magento\Backend\Model\Session\Quote::class);
        $session->setCustomerId(null);
        $this->assertFalse(
            $this->_model->getCustomerWishlist(true),
            'If customer ID is not set to session, false is expected to be returned.'
        );
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     */
    public function testGetCustomerWishlist()
    {
        $customerIdFromFixture = 1;
        $productIdFromFixture = 1;
        /** @var \Magento\Backend\Model\Session\Quote $session */
        $session = Bootstrap::getObjectManager()->create(\Magento\Backend\Model\Session\Quote::class);
        $session->setCustomerId($customerIdFromFixture);

        /** Test new wishlist creation for the customer specified above */
        /** @var \Magento\Wishlist\Model\Wishlist $wishlist */
        $wishlist = $this->_model->getCustomerWishlist(true);
        $this->assertInstanceOf(
            \Magento\Wishlist\Model\Wishlist::class,
            $wishlist,
            'New Wish List is expected to be created if existing Customer does not have one yet.'
        );
        $this->assertEquals(0, $wishlist->getItemsCount(), 'New Wish List must be empty just after creation.');

        /** Add new item to wishlist and try to get it using getCustomerWishlist once again */
        $wishlist->addNewItem($productIdFromFixture)->save();
        $updatedWishlist = $this->_model->getCustomerWishlist(true);
        $this->assertEquals(
            1,
            $updatedWishlist->getItemsCount(),
            'Wish List must contain a Product which was added to it earlier.'
        );

        /** Try to load wishlist from cache in the class after it is deleted from DB */
        $wishlist->delete();
        $this->assertSame(
            $updatedWishlist,
            $this->_model->getCustomerWishlist(false),
            'Wish List cached in class variable is expected to be returned.'
        );
        $this->assertNotSame(
            $updatedWishlist,
            $this->_model->getCustomerWishlist(true),
            'New Wish List is expected to be created when cache is forced to be refreshed.'
        );
    }

    /**
     * @magentoDbIsolation enabled
     */
    public function testSetBillingAddress()
    {
        $addressData = $this->_getValidAddressData();
        /** Validate data before creating address object */
        $this->_model->setIsValidate(true)->setBillingAddress($addressData);
        $this->assertInstanceOf(
            \Magento\Quote\Model\Quote\Address::class,
            $this->_model->getBillingAddress(),
            'Billing address object was not created.'
        );

        $expectedAddressData = array_merge(
            $addressData,
            [
                'address_type' => 'billing',
                'quote_id' => $this->_model->getQuote()->getId(),
                'street' => "Line1\nLine2",
                'save_in_address_book' => 0,
                'region' => '',
                'region_id' => 1,
            ]
        );

        $result = $this->_model->getBillingAddress()->getData();
        foreach ($expectedAddressData as $key => $value) {
            $this->assertArrayHasKey($key, $result);
            $this->assertEquals($value, $result[$key]);
        }
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     */
    public function testSetBillingAddressValidationErrors()
    {
        $customerIdFromFixture = 1;
        /** @var \Magento\Backend\Model\Session\Quote $session */
        $session = Bootstrap::getObjectManager()->create(\Magento\Backend\Model\Session\Quote::class);
        $session->setCustomerId($customerIdFromFixture);
        $invalidAddressData = array_merge($this->_getValidAddressData(), ['firstname' => '', 'lastname' => '']);
        /**
         * Note that validation errors are collected during setBillingAddress() call in the internal class variable,
         * but they are not set to message manager at this step.
         * They are set to message manager only during createOrder() call.
         */
        $this->_model->setIsValidate(true)->setBillingAddress($invalidAddressData);
        try {
            $this->_model->createOrder();
            $this->fail('Validation errors are expected to lead to exception during createOrder() call.');
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            /** createOrder is expected to throw exception with empty message when validation error occurs */
        }
        $errorMessages = [];
        /** @var $validationError \Magento\Framework\Message\Error */
        foreach ($this->_messageManager->getMessages()->getItems() as $validationError) {
            $errorMessages[] = $validationError->getText();
        }
        $this->assertTrue(
            in_array('Billing Address: "First Name" is a required value.', $errorMessages),
            'Expected validation message is absent.'
        );
        $this->assertTrue(
            in_array('Billing Address: "Last Name" is a required value.', $errorMessages),
            'Expected validation message is absent.'
        );
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoDbIsolation enabled
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
            'billing_address' => array_merge($this->_getValidAddressData(), ['save_in_address_book' => '1']),
            'shipping_address' => array_merge(
                $this->_getValidAddressData(),
                ['save_in_address_book' => '1', 'firstname' => $firstNameForShippingAddress]
            ),
            'shipping_method' => $shippingMethod,
            'comment' => ['customer_note' => ''],
            'send_confirmation' => true,
        ];
        $paymentData = ['method' => $paymentMethod];

        $this->_preparePreconditionsForCreateOrder(
            $productIdFromFixture,
            $customerEmail,
            $shippingMethod,
            $shippingAddressAsBilling,
            $paymentData,
            $orderData,
            $paymentMethod
        );
        $order = $this->_model->createOrder();
        $this->_verifyCreatedOrder($order, $shippingMethod);
        /** @var \Magento\Customer\Model\Customer $customer */
        $customer = Bootstrap::getObjectManager()->create(\Magento\Customer\Model\Customer::class);
        $customer->load($order->getCustomerId());
        $this->assertEquals(
            $firstNameForShippingAddress,
            $customer->getPrimaryShippingAddress()->getFirstname(),
            'Shipping address is saved incorrectly.'
        );
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoDbIsolation enabled
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
            'billing_address' => array_merge($this->_getValidAddressData(), ['save_in_address_book' => '1']),
            'shipping_method' => $shippingMethod,
            'comment' => ['customer_note' => ''],
            'send_confirmation' => false,
        ];
        $paymentData = ['method' => $paymentMethod];

        $this->_preparePreconditionsForCreateOrder(
            $productIdFromFixture,
            $customerEmail,
            $shippingMethod,
            $shippingAddressAsBilling,
            $paymentData,
            $orderData,
            $paymentMethod
        );
        $order = $this->_model->createOrder();
        $this->_verifyCreatedOrder($order, $shippingMethod);
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDbIsolation enabled
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
            'billing_address' => array_merge($this->_getValidAddressData(), ['save_in_address_book' => '1']),
            'shipping_address' => array_merge(
                $this->_getValidAddressData(),
                ['save_in_address_book' => '1', 'firstname' => $firstNameForShippingAddress]
            ),
            'shipping_method' => $shippingMethod,
            'comment' => ['customer_note' => ''],
            'send_confirmation' => false,
        ];
        $paymentData = ['method' => $paymentMethod];

        $this->_preparePreconditionsForCreateOrder(
            $productIdFromFixture,
            $customerEmailFromFixture,
            $shippingMethod,
            $shippingAddressAsBilling,
            $paymentData,
            $orderData,
            $paymentMethod,
            $customerIdFromFixture
        );
        $order = $this->_model->createOrder();
        $this->_verifyCreatedOrder($order, $shippingMethod);
        $customer = $this->getCustomerById($order->getCustomerId());
        $address = $this->getAddressById($customer->getDefaultShipping());
        $this->assertEquals(
            $firstNameForShippingAddress,
            $address->getFirstname(),
            'Shipping address is saved incorrectly.'
        );
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDbIsolation enabled
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
            'billing_address' => array_merge($this->_getValidAddressData(), ['save_in_address_book' => '1']),
            'shipping_method' => $shippingMethod,
            'comment' => ['customer_note' => ''],
            'send_confirmation' => false,
        ];
        $paymentData = ['method' => $paymentMethod];

        $this->_preparePreconditionsForCreateOrder(
            $productIdFromFixture,
            $customerEmailFromFixture,
            $shippingMethod,
            $shippingAddressAsBilling,
            $paymentData,
            $orderData,
            $paymentMethod,
            $customerIdFromFixture
        );
        $order = $this->_model->createOrder();
        $this->_verifyCreatedOrder($order, $shippingMethod);
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/quote.php
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoAppIsolation enabled
     */
    public function testGetCustomerCartExistingCart()
    {
        $fixtureCustomerId = 1;

        /** Preconditions */
        /** @var \Magento\Backend\Model\Session\Quote $session */
        $session = Bootstrap::getObjectManager()->create(\Magento\Backend\Model\Session\Quote::class);
        $session->setCustomerId($fixtureCustomerId);
        /** @var $quoteFixture \Magento\Quote\Model\Quote */
        $quoteFixture = Bootstrap::getObjectManager()->create(\Magento\Quote\Model\Quote::class);
        $quoteFixture->load('test01', 'reserved_order_id');
        $quoteFixture->setCustomerIsGuest(false)->setCustomerId($fixtureCustomerId)->save();

        /** SUT execution */
        $customerQuote = $this->_model->getCustomerCart();
        $this->assertEquals($quoteFixture->getId(), $customerQuote->getId(), 'Quote ID is invalid.');

        /** Try to load quote once again to ensure that caching works correctly */
        $customerQuoteFromCache = $this->_model->getCustomerCart();
        $this->assertSame($customerQuote, $customerQuoteFromCache, 'Customer quote caching does not work correctly.');
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/quote.php
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoAppIsolation enabled
     */
    public function testMoveQuoteItemToCart()
    {
        $fixtureCustomerId = 1;

        /** Preconditions */
        /** @var \Magento\Backend\Model\Session\Quote $session */
        $session = Bootstrap::getObjectManager()->create(\Magento\Backend\Model\Session\Quote::class);
        $session->setCustomerId($fixtureCustomerId);
        /** @var $quoteFixture \Magento\Quote\Model\Quote */
        $quoteFixture = Bootstrap::getObjectManager()->create(\Magento\Quote\Model\Quote::class);
        $quoteFixture->load('test01', 'reserved_order_id');
        $quoteFixture->setCustomerIsGuest(false)->setCustomerId($fixtureCustomerId)->save();

        $customerQuote = $this->_model->getCustomerCart();
        $item = $customerQuote->getAllVisibleItems()[0];

        $this->_model->moveQuoteItem($item, 'cart', 3);
        $this->assertEquals(4, $item->getQty(), 'Number of Qty isn\'t correct for Quote item.');
        $this->assertEquals(3, $item->getQtyToAdd(), 'Number of added qty isn\'t correct for Quote item.');
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testGetCustomerCartNewCart()
    {
        $customerIdFromFixture = 1;
        $customerEmailFromFixture = 'customer@example.com';

        /** Preconditions */
        /** @var \Magento\Backend\Model\Session\Quote $session */
        $session = Bootstrap::getObjectManager()->create(\Magento\Backend\Model\Session\Quote::class);
        $session->setCustomerId($customerIdFromFixture);

        /** SUT execution */
        $customerQuote = $this->_model->getCustomerCart();
        $this->assertNotEmpty($customerQuote->getId(), 'Quote ID is invalid.');
        $this->assertEquals(
            $customerEmailFromFixture,
            $customerQuote->getCustomerEmail(),
            'Customer data is preserved incorrectly in a newly quote.'
        );
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
    protected function _preparePreconditionsForCreateOrder(
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
        $product = Bootstrap::getObjectManager()->create(\Magento\Catalog\Model\Product::class);
        $product->load($productIdFromFixture)->setHasOptions(false)->save();

        /** Set current customer */
        /** @var \Magento\Backend\Model\Session\Quote $session */
        $session = Bootstrap::getObjectManager()->get(\Magento\Backend\Model\Session\Quote::class);
        if ($customerIdFromFixture !== null) {
            $session->setCustomerId($customerIdFromFixture);

            /** Unset fake IDs for default billing and shipping customer addresses */
            /** @var \Magento\Customer\Model\Customer $customer */
            $customer = Bootstrap::getObjectManager()->create(\Magento\Customer\Model\Customer::class);
            $customer->load($customerIdFromFixture)->setDefaultBilling(null)->setDefaultShipping(null)->save();
        } else {
            /**
             * Customer ID must be set to session to pass \Magento\Sales\Model\AdminOrder\Create::_validate()
             * This code emulates order placement via admin panel.
             */
            $session->setCustomerId(0);
        }

        /** Emulate availability of shipping method (all are disabled by default) */
        /** @var $rate \Magento\Quote\Model\Quote\Address\Rate */
        $rate = Bootstrap::getObjectManager()->create(\Magento\Quote\Model\Quote\Address\Rate::class);
        $rate->setCode($shippingMethod);
        $this->_model->getQuote()->getShippingAddress()->addShippingRate($rate);

        $this->_model->setShippingAsBilling($shippingAddressAsBilling);
        $this->_model->addProduct($productIdFromFixture, ['qty' => 1]);
        $this->_model->setPaymentData($paymentData);
        $this->_model->setIsValidate(true)->importPostData($orderData);

        /** Check preconditions */

        $this->assertEquals(
            0,
            $this->_messageManager->getMessages()->getCount(),
            "Precondition failed: Errors occurred before SUT execution."
        );
        /** Selectively check quote data */
        $createOrderData = $this->_model->getData();
        $this->assertEquals(
            $shippingMethod,
            $createOrderData['shipping_method'],
            'Precondition failed: Shipping method specified in create order model is invalid'
        );
        $this->assertEquals(
            'FirstName',
            $createOrderData['billing_address']['firstname'],
            'Precondition failed: Address data is invalid in create order model'
        );
        $this->assertEquals(
            'Simple Product',
            $this->_model->getQuote()->getItemByProduct($product)->getData('name'),
            'Precondition failed: Quote items data is invalid in create order model'
        );
        $this->assertEquals(
            $customerEmail,
            $this->_model->getQuote()->getCustomer()->getEmail(),
            'Precondition failed: Customer data is invalid in create order model'
        );
        $this->assertEquals(
            $paymentMethod,
            $this->_model->getQuote()->getPayment()->getData('method'),
            'Precondition failed: Payment method data is invalid in create order model'
        );
    }

    /**
     * Ensure that order is created correctly via createOrder().
     *
     * @param \Magento\Sales\Model\Order $order
     * @param string $shippingMethod
     */
    protected function _verifyCreatedOrder($order, $shippingMethod)
    {
        /** Selectively check order data */
        $orderData = $order->getData();
        $this->assertNotEmpty($orderData['increment_id'], 'Order increment ID is empty.');
        $this->assertEquals($this->_model->getQuote()->getId(), $orderData['quote_id'], 'Quote ID is invalid.');
        $this->assertEquals(
            $this->_model->getQuote()->getCustomer()->getEmail(),
            $orderData['customer_email'],
            'Customer email is invalid.'
        );
        $this->assertEquals(
            $this->_model->getQuote()->getCustomer()->getFirstname(),
            $orderData['customer_firstname'],
            'Customer first name is invalid.'
        );
        $this->assertEquals($shippingMethod, $orderData['shipping_method'], 'Shipping method is invalid.');
    }

    /**
     * Get valid address data for address creation.
     *
     * @return array
     */
    protected function _getValidAddressData()
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
     * @param int $id
     * @return \Magento\Customer\Api\Data\CustomerInterface
     */
    private function getCustomerById($id)
    {
        return $this->getCustomerRepository()->getById($id);
    }

    /**
     * @return \Magento\Customer\Api\CustomerRepositoryInterface
     */
    private function getCustomerRepository()
    {
        return Bootstrap::getObjectManager()->create(\Magento\Customer\Api\CustomerRepositoryInterface::class);
    }

    /**
     * @param int $id
     * @return \Magento\Customer\Api\Data\AddressInterface
     */
    private function getAddressById($id)
    {
        return $this->getAddressRepository()->getById($id);
    }

    /**
     * @return \Magento\Customer\Api\AddressRepositoryInterface
     */
    private function getAddressRepository()
    {
        /** @var \Magento\Customer\Api\AddressRepositoryInterface $addressRepository */
        return Bootstrap::getObjectManager()->create(\Magento\Customer\Api\AddressRepositoryInterface::class);
    }
}
