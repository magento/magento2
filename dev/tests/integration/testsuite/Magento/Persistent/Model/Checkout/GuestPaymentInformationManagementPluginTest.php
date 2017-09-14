<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Persistent\Model\Checkout;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class GuestPaymentInformationManagementPluginTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Persistent\Helper\Session
     */
    protected $persistentSessionHelper;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $cartRepository;

    /**
     * @var \Magento\Quote\Api\CartManagementInterface
     */
    protected $cartManagement;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerFactory;

    /**
     * @var \Magento\Quote\Api\CartItemRepositoryInterface
     */
    protected $cartItemRepository;

    /**
     * @var \Magento\Quote\Model\QuoteIdMask
     */
    protected $quoteIdMaskFactory;

    /**
     * @var \Magento\Quote\Api\PaymentMethodManagementInterface
     */
    protected $paymentMethodManagement;

    /**
     * @var \Magento\Quote\Api\BillingAddressManagementInterface
     */
    protected $billingAddressManagement;

    /**
     * @var \Magento\Quote\Model\ShippingAddressManagementInterface
     */
    protected $shippingAddressManagement;

    /**
     * @var \Magento\Quote\Api\ShippingMethodManagementInterface
     */
    protected $shippingEstimateManagement;

    /**
     * @var \Magento\Checkout\Api\TotalsInformationManagementInterface
     */
    protected $totalsInformationManagement;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    public function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->customerSession = $this->objectManager->get(\Magento\Customer\Model\Session::class);
        $this->persistentSessionHelper = $this->objectManager->create(\Magento\Persistent\Helper\Session::class);
        $this->customerFactory = $this->objectManager->create(
            \Magento\Customer\Model\CustomerFactory::class
        );
        $this->checkoutSession = $this->objectManager->create(\Magento\Checkout\Model\Session::class);
        $this->cartRepository = $this->objectManager->create(\Magento\Quote\Api\CartRepositoryInterface::class);
        $this->cartManagement = $this->objectManager->create(\Magento\Quote\Api\CartManagementInterface::class);
        $this->cartItemRepository = $this->objectManager->create(\Magento\Quote\Api\CartItemRepositoryInterface::class);
        $this->quoteIdMaskFactory = $this->objectManager->create(\Magento\Quote\Model\QuoteIdMaskFactory::class);
        $this->paymentMethodManagement = $this->objectManager->create(
            \Magento\Quote\Api\PaymentMethodManagementInterface::class
        );
        $this->billingAddressManagement = $this->objectManager->create(
            \Magento\Quote\Api\BillingAddressManagementInterface::class
        );
        $this->shippingEstimateManagement = $this->objectManager->create(
            \Magento\Quote\Api\ShippingMethodManagementInterface::class
        );
        $this->totalsInformationManagement = $this->objectManager->create(
            \Magento\Checkout\Api\TotalsInformationManagementInterface::class
        );
        $this->shippingAddressManagement = $this->objectManager->create(
            \Magento\Quote\Model\ShippingAddressManagementInterface::class
        );
    }

    /**
     * Test builds out a persistent customer shopping cart, emulates a
     * session expiring, and checks out with the persisted cart as a guest.
     *
     * Expected - Order contains guest email, not customer email.
     *
     * @magentoConfigFixture current_store persistent/options/customer 1
     * @magentoConfigFixture current_store persistent/options/enabled 1
     * @magentoConfigFixture current_store persistent/options/remember_enabled 1
     * @magentoConfigFixture current_store persistent/options/remember_default 1
     * @magentoConfigFixture current_store payment/substitution/active 1
     * @magentoAppArea frontend
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Customer/_files/customer_address.php
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoDbIsolation disabled
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testBeforeSavePaymentInformationAndPlaceOrder()
    {
        $guestEmail = 'guest@example.com';

        //Retrieve customer from repository
        /** @var \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository */
        $customerRepository = $this->objectManager->create(\Magento\Customer\Api\CustomerRepositoryInterface::class);
        $customer = $customerRepository->getById(1);
        $this->customerSession->loginById($customer->getId());

        //Retrieve product from repository
        /** @var \Magento\Catalog\Api\ProductRepositoryInterface $productRepository */
        $productRepository = $this->objectManager->create(\Magento\Catalog\Api\ProductRepositoryInterface::class);
        $product = $productRepository->getById(1);
        $product->setOptions(null);
        $productRepository->save($product);

        //Add item to newly created customer cart
        $cartId = $this->cartManagement->createEmptyCartForCustomer($customer->getId());
        /** @var \Magento\Quote\Api\Data\CartItemInterface $quoteItem */
        $quoteItem = $this->objectManager->create(\Magento\Quote\Api\Data\CartItemInterface::class);
        $quoteItem->setQuoteId($cartId);
        $quoteItem->setProduct($product);
        $quoteItem->setQty(2);
        $this->cartItemRepository->save($quoteItem);

        //Fill out address data
        /** @var \Magento\Quote\Api\Data\AddressInterface $billingAddress */
        $billingAddress = $this->objectManager->create(\Magento\Quote\Api\Data\AddressInterface::class);
        $billingAddress->setFirstname('guestFirst');
        $billingAddress->setLastname('guestLast');
        $billingAddress->setEmail($guestEmail);
        $billingAddress->setStreet('guestStreet');
        $billingAddress->setCity('Austin');
        $billingAddress->setTelephone('1342587690');
        $billingAddress->setPostcode('14325');
        $billingAddress->setRegionId(12);
        $billingAddress->setCountryId('US');
        /** @var \Magento\Quote\Api\Data\AddressInterface $shippingAddress */
        $shippingAddress = $this->objectManager->create(\Magento\Quote\Api\Data\AddressInterface::class);
        $shippingAddress->setFirstname('guestFirst');
        $shippingAddress->setLastname('guestLast');
        $shippingAddress->setEmail(null);
        $shippingAddress->setStreet('guestStreet');
        $shippingAddress->setCity('Austin');
        $shippingAddress->setTelephone('1342587690');
        $shippingAddress->setPostcode('14325');
        $shippingAddress->setRegionId(12);
        $shippingAddress->setCountryId('US');
        $shippingAddress->setSameAsBilling(true);
        $this->shippingAddressManagement->assign($cartId, $shippingAddress);
        $shippingAddress = $this->shippingAddressManagement->get($cartId);

        //Determine shipping options and collect totals
        /** @var \Magento\Checkout\Api\Data\TotalsInformationInterface $totals */
        $totals = $this->objectManager->create(\Magento\Checkout\Api\Data\TotalsInformationInterface::class);
        $totals->setAddress($shippingAddress);
        $totals->setShippingCarrierCode('flatrate');
        $totals->setShippingMethodCode('flatrate');
        $this->totalsInformationManagement->calculate($cartId, $totals);

        //Select payment method
        /** @var \Magento\Quote\Api\Data\PaymentInterface $payment */
        $payment = $this->objectManager->create(\Magento\Quote\Api\Data\PaymentInterface::class);
        $payment->setMethod('checkmo');
        $this->paymentMethodManagement->set($cartId, $payment);
        $quote = $this->cartRepository->get($cartId);

        //Verify checkout session contains correct quote data
        $this->checkoutSession->clearQuote();
        $this->checkoutSession->setQuoteId($quote->getId());

        //Set up persistent session data and expire customer session
        $this->persistentSessionHelper->getSession()->setCustomerId($customer->getId())
            ->setPersistentCookie(10000, '');
        $this->persistentSessionHelper->getSession()->removePersistentCookie()->setPersistentCookie(100000000, '');
        $this->customerSession->setIsCustomerEmulated(true)->expireSessionCookie();

        //Grab masked quote Id to pass to payment manager
        /** @var $quoteIdMask \Magento\Quote\Model\QuoteIdMask */
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load(
            $this->checkoutSession->getQuote()->getId(),
            'quote_id'
        );
        $maskedCartId = $quoteIdMask->getMaskedId();

        //Submit order as expired/emulated customer
        /** @var \Magento\Checkout\Model\GuestPaymentInformationManagement $paymentManagement */
        $paymentManagement = $this->objectManager->create(
            \Magento\Checkout\Model\GuestPaymentInformationManagement::class
        );

        //Grab created order data
        $orderId = $paymentManagement->savePaymentInformationAndPlaceOrder(
            $maskedCartId,
            $guestEmail,
            $quote->getPayment(),
            $billingAddress
        );
        /** @var \Magento\Sales\Api\OrderRepositoryInterface $orderRepo */
        $orderRepo = $this->objectManager->create(\Magento\Sales\Api\OrderRepositoryInterface::class);
        $order = $orderRepo->get($orderId);

        //Assert order tied to guest email
        $this->assertEquals($guestEmail, $order->getCustomerEmail());
    }
}
