<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LoginAsCustomerSales\Plugin;

use Magento\Customer\Model\Session as CustomerSession;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\LoginAsCustomer\Api\AuthenticateCustomerInterface;

/**
 * Class AuthenticateCustomer Plugin
 */
class AuthenticateCustomer
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
     * @param int $customerId
     * @param int $adminId
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeExecute(
        AuthenticateCustomerInterface $subject,
        int $customerId,
        int $adminId
    ) {
        if (!$this->customerSession->getId()) {
            $quote = $this->checkoutSession->getQuote();
            /* Remove items from guest cart */
            foreach ($quote->getAllVisibleItems() as $item) {
                $quote->removeItem($item->getId());
            }
            $this->quoteRepository->save($quote);
        }
    }

    /**
     * Mark customer cart as not guest
     *
     * @param int $adminId
     * @param AuthenticateCustomerInterface $subject
     * @param bool $result
     * @param int $customerId
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterExecute(
        AuthenticateCustomerInterface $subject,
        bool $result,
        int $customerId,
        int $adminId
    ) {
        if ($result) {
            $this->checkoutSession->loadCustomerQuote();

            $quote = $this->checkoutSession->getQuote();
            $quote->setCustomerIsGuest(0);
            $this->quoteRepository->save($quote);
        }
        return $result;
    }
}
