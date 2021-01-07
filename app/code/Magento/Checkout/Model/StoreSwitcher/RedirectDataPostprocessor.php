<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Checkout\Model\StoreSwitcher;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Store\Model\StoreSwitcher\ContextInterface;
use Magento\Store\Model\StoreSwitcher\RedirectDataPostprocessorInterface;
use Psr\Log\LoggerInterface;

/**
 * Process checkout data redirected from origin store
 *
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class RedirectDataPostprocessor implements RedirectDataPostprocessorInterface
{
    /**
     * @var CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param CartRepositoryInterface $quoteRepository
     * @param CustomerSession $customerSession
     * @param CheckoutSession $checkoutSession
     * @param LoggerInterface $logger
     */
    public function __construct(
        CartRepositoryInterface $quoteRepository,
        CustomerSession $customerSession,
        CheckoutSession $checkoutSession,
        LoggerInterface $logger
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->customerSession = $customerSession;
        $this->checkoutSession = $checkoutSession;
        $this->logger = $logger;
    }

    /**
     * @inheritDoc
     */
    public function process(ContextInterface $context, array $data): void
    {
        if (!empty($data['quote_id'])
            && $this->checkoutSession->getQuoteId() === null
            && !$this->customerSession->isLoggedIn()
        ) {
            try {
                $quote = $this->quoteRepository->get((int) $data['quote_id']);
                if ($quote
                    && $quote->getIsActive()
                    && in_array($context->getTargetStore()->getId(), $quote->getSharedStoreIds())
                ) {
                    $this->checkoutSession->setQuoteId($quote->getId());
                }
            } catch (\Throwable $e) {
                $this->logger->error($e);
            }
        }
        $quote = $this->checkoutSession->getQuote();
        if ($quote->getIsActive()) {
            // Update quote items so that product names are updated for current store view
            $quote->setStoreId($context->getTargetStore()->getId());
            $quote->getItemsCollection(false);
            $this->quoteRepository->save($quote);
        }
    }
}
