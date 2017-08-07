<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Persistent\Model\Checkout;

use Magento\Checkout\Model\GuestPaymentInformationManagement;
use Magento\Checkout\Model\Session;

/**
 * Plugin to convert shopping cart from persistent cart to guest cart before order save when customer not logged in
 * @since 2.2.0
 */
class GuestPaymentInformationManagementPlugin
{
    /**
     * Persistence Session Helper
     *
     * @var \Magento\Persistent\Helper\Session
     * @since 2.2.0
     */
    private $persistenceSessionHelper;

    /**
     * Persistence Data Helper
     *
     * @var \Magento\Persistent\Helper\Data
     * @since 2.2.0
     */
    private $persistenceDataHelper;

    /**
     * Customer Session
     *
     * @var \Magento\Customer\Model\Session
     * @since 2.2.0
     */
    private $customerSession;

    /**
     * Checkout Session
     *
     * @var \Magento\Checkout\Model\Session
     * @since 2.2.0
     */
    private $checkoutSession;

    /**
     * Quote Manager
     *
     * @var \Magento\Persistent\Model\QuoteManager
     * @since 2.2.0
     */
    private $quoteManager;

    /**
     * Cart Repository
     *
     * @var \Magento\Quote\Api\CartRepositoryInterface
     * @since 2.2.0
     */
    private $cartRepository;

    /**
     * Initialize dependencies
     *
     * @param \Magento\Persistent\Helper\Data $persistenceDataHelper
     * @param \Magento\Persistent\Helper\Session $persistenceSessionHelper
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Persistent\Model\QuoteManager $quoteManager
     * @param \Magento\Quote\Api\CartRepositoryInterface $cartRepository
     * @since 2.2.0
     */
    public function __construct(
        \Magento\Persistent\Helper\Data $persistenceDataHelper,
        \Magento\Persistent\Helper\Session $persistenceSessionHelper,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Persistent\Model\QuoteManager $quoteManager,
        \Magento\Quote\Api\CartRepositoryInterface $cartRepository
    ) {
        $this->persistenceDataHelper = $persistenceDataHelper;
        $this->persistenceSessionHelper = $persistenceSessionHelper;
        $this->customerSession = $customerSession;
        $this->checkoutSession = $checkoutSession;
        $this->quoteManager = $quoteManager;
        $this->cartRepository = $cartRepository;
    }

    /**
     * Convert customer cart to guest cart before order is placed if customer is not logged in
     *
     * @param GuestPaymentInformationManagement $subject
     * @param string $cartId
     * @param string $email
     * @param \Magento\Quote\Api\Data\PaymentInterface $paymentMethod
     * @param \Magento\Quote\Api\Data\AddressInterface|null $billingAddress
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.2.0
     */
    public function beforeSavePaymentInformationAndPlaceOrder(
        GuestPaymentInformationManagement $subject,
        $cartId,
        $email,
        \Magento\Quote\Api\Data\PaymentInterface $paymentMethod,
        \Magento\Quote\Api\Data\AddressInterface $billingAddress = null
    ) {
        if ($this->persistenceSessionHelper->isPersistent()
            && !$this->customerSession->isLoggedIn()
            && $this->persistenceDataHelper->isShoppingCartPersist()
            && $this->quoteManager->isPersistent()
        ) {
            $this->customerSession->setCustomerId(null);
            $this->customerSession->setCustomerGroupId(null);
            $this->quoteManager->convertCustomerCartToGuest();
            /** @var \Magento\Quote\Api\Data\CartInterface $quote */
            $quote = $this->cartRepository->get($this->checkoutSession->getQuote()->getId());
            $quote->setCustomerEmail($email);
            $quote->getAddressesCollection()->walk('setEmail', ['email' => $email]);
            $this->cartRepository->save($quote);
        }
    }
}
