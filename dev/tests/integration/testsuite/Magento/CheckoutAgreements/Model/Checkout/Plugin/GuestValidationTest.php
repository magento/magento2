<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CheckoutAgreements\Model\Checkout\Plugin;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class GuestValidationTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $checkoutSession;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @var \Magento\Quote\Api\CartManagementInterface
     */
    private $cartManagement;

    /**
     * @var \Magento\Quote\Api\CartItemRepositoryInterface
     */
    private $cartItemRepository;

    /**
     * @var \Magento\Quote\Model\QuoteIdMask
     */
    private $quoteIdMaskFactory;

    /**
     * @var \Magento\Quote\Api\PaymentMethodManagementInterface
     */
    private $paymentMethodManagement;

    /**
     * @var \Magento\Quote\Model\ShippingAddressManagementInterface
     */
    private $shippingAddressManagement;

    /**
     * @var \Magento\Checkout\Api\TotalsInformationManagementInterface
     */
    private $totalsInformationManagement;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    public function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->checkoutSession = $this->objectManager->create(\Magento\Checkout\Model\Session::class);
        $this->cartRepository = $this->objectManager->create(\Magento\Quote\Api\CartRepositoryInterface::class);
        $this->cartManagement = $this->objectManager->create(\Magento\Quote\Api\CartManagementInterface::class);
        $this->cartItemRepository = $this->objectManager->create(\Magento\Quote\Api\CartItemRepositoryInterface::class);
        $this->quoteIdMaskFactory = $this->objectManager->create(\Magento\Quote\Model\QuoteIdMaskFactory::class);
        $this->paymentMethodManagement = $this->objectManager->create(
            \Magento\Quote\Api\PaymentMethodManagementInterface::class
        );
        $this->totalsInformationManagement = $this->objectManager->create(
            \Magento\Checkout\Api\TotalsInformationManagementInterface::class
        );
        $this->shippingAddressManagement = $this->objectManager->create(
            \Magento\Quote\Model\ShippingAddressManagementInterface::class
        );
    }

    /**
     * Expected - Order fail with exception.
     *
     * @magentoConfigFixture current_store payment/substitution/active 1
     * @magentoConfigFixture default_store checkout/options/enable_agreements 1
     * @magentoDataFixture Magento/CheckoutAgreements/_files/multi_agreements_active_with_text.php
     * @magentoAppArea frontend
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoDbIsolation enabled
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @dataProvider dataProvider
     * @param string[] $agreementNames
     */
    public function testBeforeSavePaymentInformationAndPlaceOrder($agreementNames)
    {
        $guestEmail = 'guest@example.com';
        $carrierCode = 'flatrate';
        $shippingMethodCode = 'flatrate';
        $paymentMethod = 'checkmo';
        $paymentExtension = $this->getPaymentExtension($agreementNames);
        $product = $this->getProduct(1);
        $shippingAddress = $this->getShippingAddress();
        $billingAddress = $this->getBillingAddress($guestEmail);
        $payment = $this->getPayment($paymentMethod, $paymentExtension);

        //Create cart and add product to it
        $cartId = $this->cartManagement->createEmptyCart();
        $this->addProductToCart($product, $cartId);

        //Assign shipping address
        $this->shippingAddressManagement->assign($cartId, $shippingAddress);
        $shippingAddress = $this->shippingAddressManagement->get($cartId);

        //Calculate totals
        $totals = $this->getTotals($shippingAddress, $carrierCode, $shippingMethodCode);
        $this->totalsInformationManagement->calculate($cartId, $totals);

        //Set payment method
        $this->paymentMethodManagement->set($cartId, $payment);

        //Verify checkout session contains correct quote data
        $quote = $this->cartRepository->get($cartId);
        $this->checkoutSession->clearQuote();
        $this->checkoutSession->setQuoteId($quote->getId());

        //Grab masked quote Id to pass to payment manager
        /** @var $quoteIdMask \Magento\Quote\Model\QuoteIdMask */
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load(
            $this->checkoutSession->getQuote()->getId(),
            'quote_id'
        );
        $maskedCartId = $quoteIdMask->getMaskedId();

        $paymentManagement = $this->objectManager->create(
            \Magento\Checkout\Model\GuestPaymentInformationManagement::class
        );

        try {
            $orderId = $paymentManagement->savePaymentInformationAndPlaceOrder(
                $maskedCartId,
                $guestEmail,
                $payment,
                $billingAddress
            );
            $this->assertNotNull($orderId);
        } catch (\Magento\Framework\Exception\CouldNotSaveException $e) {
            $this->assertEquals(
                __('Please agree to all the terms and conditions before placing the order.'),
                $e->getMessage()
            );
        }
    }

    public function dataProvider()
    {
        return [
            [[]],
            [['First Checkout Agreement (active)']],
            [['First Checkout Agreement (active)', 'Second Checkout Agreement (active)']]
        ];
    }

    /**
     * @param string $guestEmail
     * @return \Magento\Quote\Api\Data\AddressInterface
     */
    private function getBillingAddress($guestEmail)
    {
        /** @var \Magento\Quote\Api\Data\AddressInterface $billingAddress */
        $billingAddress = $this->objectManager->create(\Magento\Quote\Api\Data\AddressInterface::class);
        $billingAddress->setFirstname('First');
        $billingAddress->setLastname('Last');
        $billingAddress->setEmail($guestEmail);
        $billingAddress->setStreet('Street');
        $billingAddress->setCity('City');
        $billingAddress->setTelephone('1234567890');
        $billingAddress->setPostcode('12345');
        $billingAddress->setRegionId(12);
        $billingAddress->setCountryId('US');
        return $billingAddress;
    }

    /**
     * @param string $agreementName
     * @return \Magento\CheckoutAgreements\Model\Agreement
     */
    private function getAgreement($agreementName)
    {
        $agreement = $this->objectManager->create(\Magento\CheckoutAgreements\Model\Agreement::class);
        $agreement->load($agreementName, 'name');
        return $agreement;
    }

    /**
     * @param string[] $agreementNames
     * @return \Magento\Quote\Api\Data\PaymentExtension
     */
    private function getPaymentExtension($agreementNames)
    {
        $agreementIds = [];
        foreach ($agreementNames as $agreementName) {
            $agreementIds[] = $this->getAgreement($agreementName)->getAgreementId();
        }
        $extension = $this->objectManager->get(\Magento\Quote\Api\Data\PaymentExtension::class);
        $extension->setAgreementIds($agreementIds);
        return $extension;
    }

    /**
     * @param int $productId
     * @return \Magento\Catalog\Api\Data\ProductInterface
     */
    private function getProduct($productId)
    {
        /** @var \Magento\Catalog\Api\ProductRepositoryInterface $productRepository */
        $productRepository = $this->objectManager->create(\Magento\Catalog\Api\ProductRepositoryInterface::class);
        $product = $productRepository->getById($productId);
        $product->setOptions(null);
        $productRepository->save($product);
        return $product;
    }

    /**
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @param string $cartId
     */
    private function addProductToCart($product, $cartId)
    {
        /** @var \Magento\Quote\Api\Data\CartItemInterface $quoteItem */
        $quoteItem = $this->objectManager->create(\Magento\Quote\Api\Data\CartItemInterface::class);
        $quoteItem->setQuoteId($cartId);
        $quoteItem->setProduct($product);
        $quoteItem->setQty(2);
        $this->cartItemRepository->save($quoteItem);
    }

    /**
     * @return \Magento\Quote\Api\Data\AddressInterface
     */
    private function getShippingAddress()
    {
        $shippingAddress = $this->objectManager->create(\Magento\Quote\Api\Data\AddressInterface::class);
        $shippingAddress->setFirstname('First');
        $shippingAddress->setLastname('Last');
        $shippingAddress->setEmail(null);
        $shippingAddress->setStreet('Street');
        $shippingAddress->setCity('City');
        $shippingAddress->setTelephone('1234567890');
        $shippingAddress->setPostcode('12345');
        $shippingAddress->setRegionId(12);
        $shippingAddress->setCountryId('US');
        $shippingAddress->setSameAsBilling(true);
        return $shippingAddress;
    }

    /**
     * @param \Magento\Quote\Api\Data\AddressInterface $shippingAddress
     * @param string $carrierCode
     * @param string $methodCode
     * @return \Magento\Checkout\Api\Data\TotalsInformationInterface
     */
    private function getTotals($shippingAddress, $carrierCode, $methodCode)
    {
        /** @var \Magento\Checkout\Api\Data\TotalsInformationInterface $totals */
        $totals = $this->objectManager->create(\Magento\Checkout\Api\Data\TotalsInformationInterface::class);
        $totals->setAddress($shippingAddress);
        $totals->setShippingCarrierCode($carrierCode);
        $totals->setShippingMethodCode($methodCode);

        return $totals;
    }

    /**
     * @param $paymentMethod
     * @param $paymentExtension
     * @return \Magento\Quote\Api\Data\PaymentInterface
     */
    private function getPayment($paymentMethod, $paymentExtension)
    {
        $payment = $this->objectManager->create(\Magento\Quote\Api\Data\PaymentInterface::class);
        $payment->setMethod($paymentMethod);
        $payment->setExtensionAttributes($paymentExtension);
        return $payment;
    }
}
