<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Plugin;

use Magento\Checkout\Model\Session;
use Magento\Quote\Model\QuoteRepository;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreSwitcherInterface;

/**
 * Updates quote items store id.
 *
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class UpdateQuoteItemStore
{
    /**
     * @var QuoteRepository
     */
    private $quoteRepository;

    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * @param QuoteRepository $quoteRepository
     * @param Session $checkoutSession
     */
    public function __construct(
        QuoteRepository $quoteRepository,
        Session $checkoutSession
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * Update store id in active quote after store view switching.
     *
     * @param StoreSwitcherInterface $subject
     * @param string $result
     * @param StoreInterface $fromStore store where we came from
     * @param StoreInterface $targetStore store where to go to
     * @param string $redirectUrl original url requested for redirect after switching
     * @return string url to be redirected after switching
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterSwitch(
        StoreSwitcherInterface $subject,
        $result,
        StoreInterface $fromStore,
        StoreInterface $targetStore,
        string $redirectUrl
    ): string {
        $quote = $this->checkoutSession->getQuote();
        if ($quote->getIsActive()) {
            $quote->setStoreId(
                $targetStore->getId()
            );
            $quote->getItemsCollection(false);
            $this->quoteRepository->save($quote);
        }
        return $result;
    }
}
