<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LoginAsCustomerSales\Plugin;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\LoginAsCustomerApi\Api\AuthenticateCustomerInterface;
use Magento\LoginAsCustomerApi\Api\Data\AuthenticationDataInterface;

/**
 * \Magento\LoginAsCustomerApi\Api\AuthenticateCustomerInterface Plugin
 *
 * Remove all items from guest shopping cart before execute. Mark customer cart as not-guest after execute
 *
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class AuthenticateCustomerPlugin
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
     * Remove all items from guest shopping cart
     *
     * @param AuthenticateCustomerInterface $subject
     * @param AuthenticationDataInterface $authenticationData
     * @return null
     * @throws LocalizedException
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeExecute(
        AuthenticateCustomerInterface $subject,
        AuthenticationDataInterface $authenticationData
    ) {
        if (!$this->customerSession->getId()) {
            $quote = $this->checkoutSession->getQuote();
            /* Remove items from guest cart */
            $quote->removeAllItems();
            $this->quoteRepository->save($quote);
        }
        return null;
    }

    /**
     * Mark customer cart as not-guest
     *
     * @param AuthenticateCustomerInterface $subject
     * @param void $result
     * @param AuthenticationDataInterface $authenticationData
     * @return void
     * @throws LocalizedException
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterExecute(
        AuthenticateCustomerInterface $subject,
        $result,
        AuthenticationDataInterface $authenticationData
    ) {
        $this->checkoutSession->loadCustomerQuote();
        $quote = $this->checkoutSession->getQuote();

        $quote->setCustomerIsGuest(0);
        $this->quoteRepository->save($quote);
    }
}
