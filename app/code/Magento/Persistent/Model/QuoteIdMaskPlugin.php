<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Persistent\Model;

use Magento\Customer\Model\Session as CustomerSession;
use Magento\Persistent\Helper\Data as PersistentData;
use Magento\Persistent\Helper\Session as PersistentSession;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\QuoteIdMask;

/**
 * Converts the active persistent customer's cart before it is used through a guest masked ID.
 *
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class QuoteIdMaskPlugin
{
    /**
     * @param PersistentData $persistentData
     * @param PersistentSession $persistentSession
     * @param CustomerSession $customerSession
     * @param CartRepositoryInterface $quoteRepository
     * @param QuoteManager $quoteManager
     */
    public function __construct(
        private readonly PersistentData $persistentData,
        private readonly PersistentSession $persistentSession,
        private readonly CustomerSession $customerSession,
        private readonly CartRepositoryInterface $quoteRepository,
        private readonly QuoteManager $quoteManager
    ) {
    }

    /**
     * Convert only the current persistent customer's quote when a guest masked ID is resolved.
     *
     * @param QuoteIdMask $subject
     * @param QuoteIdMask $result
     * @param mixed $modelId
     * @param string|null $field
     * @return QuoteIdMask
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterLoad(
        QuoteIdMask $subject,
        QuoteIdMask $result,
        $modelId,
        $field = null
    ): QuoteIdMask {
        $quoteId = (int) $result->getQuoteId();
        if ($field !== 'masked_id' || !$quoteId) {
            return $result;
        }

        if (!$this->persistentSession->isPersistent()
            || $this->customerSession->isLoggedIn()
            || !$this->persistentData->isShoppingCartPersist()
            || !$this->quoteManager->isPersistent()
        ) {
            return $result;
        }

        $persistentSession = $this->persistentSession->getSession();
        $persistentCustomerId = (int) $persistentSession->getCustomerId();
        if (!$persistentCustomerId) {
            return $result;
        }

        $quote = $this->quoteRepository->get($quoteId);
        if ((int) $quote->getCustomerId() !== $persistentCustomerId) {
            return $result;
        }

        $this->quoteManager->convertCustomerCartToGuest($quote);
        $this->customerSession->setCustomerId(null);
        $this->customerSession->setCustomerGroupId(null);
        $persistentSession->removePersistentCookie();
        $this->persistentSession->setSession(null);

        return $result;
    }
}
