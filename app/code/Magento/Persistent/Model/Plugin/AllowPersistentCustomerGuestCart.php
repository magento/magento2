<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Persistent\Model\Plugin;

use Magento\Customer\Model\Session as CustomerSession;
use Magento\Persistent\Helper\Data as PersistentHelper;
use Magento\Persistent\Helper\Session as PersistentSession;
use Magento\Quote\Model\GuestCart\GetGuestCart;

/**
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class AllowPersistentCustomerGuestCart
{
    /**
     * @param CustomerSession $customerSession
     * @param PersistentSession $persistentSession
     * @param PersistentHelper $persistentHelper
     */
    public function __construct(
        private readonly CustomerSession $customerSession,
        private readonly PersistentSession $persistentSession,
        private readonly PersistentHelper $persistentHelper
    ) {
    }

    /**
     * Allow the guest checkout endpoints to read a cart that still belongs to a persisted customer.
     *
     * @param GetGuestCart $subject
     * @param callable $proceed
     * @param int $customerId
     * @param string $maskedCartId
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundCheckIsGuestCart(
        GetGuestCart $subject,
        callable $proceed,
        int $customerId,
        string $maskedCartId
    ): void {
        $isOwnedByPersistedCustomer = $customerId !== 0
            && $this->persistentHelper->isShoppingCartPersist()
            && !$this->customerSession->isLoggedIn()
            && $this->persistentSession->isPersistent()
            && (int) $this->persistentSession->getSession()->getCustomerId() === $customerId;

        if (!$isOwnedByPersistedCustomer) {
            $proceed($customerId, $maskedCartId);
        }
    }
}
