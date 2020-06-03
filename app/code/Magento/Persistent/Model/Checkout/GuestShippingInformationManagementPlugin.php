<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Persistent\Model\Checkout;

use Magento\Checkout\Api\Data\PaymentDetailsInterface;
use Magento\Checkout\Model\GuestShippingInformationManagement;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Persistent\Helper\Data;
use Magento\Persistent\Helper\Session as PersistentSession;
use Magento\Persistent\Model\QuoteManager;

/**
 * Plugin to convert shopping cart from persistent cart to guest cart after shipping information saved
 *
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class GuestShippingInformationManagementPlugin
{
    /**
     * Persistence Session Helper
     *
     * @var PersistentSession
     */
    private $persistenceSessionHelper;

    /**
     * Persistence Data Helper
     *
     * @var Data
     */
    private $persistenceDataHelper;

    /**
     * Customer Session
     *
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * Quote Manager
     *
     * @var QuoteManager
     */
    private $quoteManager;

    /**
     * Initialize dependencies
     *
     * @param Data $persistenceDataHelper
     * @param PersistentSession $persistenceSessionHelper
     * @param CustomerSession $customerSession
     * @param QuoteManager $quoteManager
     */
    public function __construct(
        Data $persistenceDataHelper,
        PersistentSession $persistenceSessionHelper,
        CustomerSession $customerSession,
        QuoteManager $quoteManager
    ) {
        $this->persistenceDataHelper = $persistenceDataHelper;
        $this->persistenceSessionHelper = $persistenceSessionHelper;
        $this->customerSession = $customerSession;
        $this->quoteManager = $quoteManager;
    }

    /**
     * Convert shopping cart from persistent cart to guest cart after shipping information saved
     *
     * Check if shopping cart is persistent and customer is not logged in, and only one payment method is available,
     * then converts the shopping cart guest cart.
     * If only one payment is available, it's preselected by default and the payment information is automatically saved.
     *
     * @param GuestShippingInformationManagement $subject
     * @param PaymentDetailsInterface $result
     * @return PaymentDetailsInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterSaveAddressInformation(
        GuestShippingInformationManagement $subject,
        PaymentDetailsInterface $result
    ): PaymentDetailsInterface {
        if ($this->persistenceSessionHelper->isPersistent()
            && !$this->customerSession->isLoggedIn()
            && $this->persistenceDataHelper->isShoppingCartPersist()
            && $this->quoteManager->isPersistent()
            && count($result->getPaymentMethods()) === 1
        ) {
            $this->customerSession->setCustomerId(null);
            $this->customerSession->setCustomerGroupId(null);
            $this->quoteManager->convertCustomerCartToGuest();
        }
        return $result;
    }
}
