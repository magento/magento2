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
use Magento\Store\Api\StoreCookieManagerInterface;

/**
 * Updates quote store id.
 */
class UpdateQuoteStore
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
     * @param StoreCookieManagerInterface $subject
     * @param null $result
     * @param StoreInterface $store
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterSetStoreCookie(
        StoreCookieManagerInterface $subject,
        $result,
        StoreInterface $store
    ) {
        $storeCodeFromCookie = $subject->getStoreCodeFromCookie();
        if (null === $storeCodeFromCookie) {
            return;
        }

        $quote = $this->checkoutSession->getQuote();
        if ($quote->getIsActive() && $store->getCode() != $storeCodeFromCookie) {
            $quote->setStoreId(
                $store->getId()
            );
            $this->quoteRepository->save($quote);
        }
    }
}
