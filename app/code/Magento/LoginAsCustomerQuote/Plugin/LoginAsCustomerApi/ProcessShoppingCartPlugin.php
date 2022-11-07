<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LoginAsCustomerQuote\Plugin\LoginAsCustomerApi;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\LoginAsCustomerApi\Api\AuthenticateCustomerBySecretInterface;

/**
 * Remove all items from guest shopping cart and mark cart as not-guest
 *
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class ProcessShoppingCartPlugin
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
     * @var CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @param CustomerSession $customerSession
     * @param CheckoutSession $checkoutSession
     * @param CartRepositoryInterface $quoteRepository
     */
    public function __construct(
        CustomerSession $customerSession,
        CheckoutSession $checkoutSession,
        CartRepositoryInterface $quoteRepository
    ) {
        $this->customerSession = $customerSession;
        $this->checkoutSession = $checkoutSession;
        $this->quoteRepository = $quoteRepository;
    }

    /**
     * Remove all items from guest shopping cart and mark cart as not-guest
     *
     * @param AuthenticateCustomerBySecretInterface $subject
     * @param string $secret
     * @return null
     * @throws LocalizedException
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeExecute(
        AuthenticateCustomerBySecretInterface $subject,
        string $secret
    ) {
        if (!$this->customerSession->getId()) {
            $quote = $this->checkoutSession->getQuote();
            /* Remove items from guest cart */
            $quote->removeAllItems();
            $quote->setCustomerIsGuest(0);
            $this->quoteRepository->save($quote);
        }
        return null;
    }
}
