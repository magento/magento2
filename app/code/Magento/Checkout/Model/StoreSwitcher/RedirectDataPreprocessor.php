<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Checkout\Model\StoreSwitcher;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Store\Model\StoreSwitcher\ContextInterface;
use Magento\Store\Model\StoreSwitcher\RedirectDataPreprocessorInterface;

/**
 * Collect checkout data to be redirected to target store
 *
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class RedirectDataPreprocessor implements RedirectDataPreprocessorInterface
{
    /**
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @param CustomerSession $customerSession
     * @param CheckoutSession $checkoutSession
     */
    public function __construct(
        CustomerSession $customerSession,
        CheckoutSession $checkoutSession
    ) {
        $this->customerSession = $customerSession;
        $this->checkoutSession = $checkoutSession;
    }
    /**
     * @inheritDoc
     */
    public function process(ContextInterface $context, array $data): array
    {
        if ($this->checkoutSession->getQuoteId() && !$this->customerSession->isLoggedIn()) {
            $quote = $this->checkoutSession->getQuote();
            if ($quote
                && $quote->getIsActive()
                && in_array($context->getTargetStore()->getId(), $quote->getSharedStoreIds())
            ) {
                $data['quote_id'] = (int) $quote->getId();
            }
        }
        return $data;
    }
}
