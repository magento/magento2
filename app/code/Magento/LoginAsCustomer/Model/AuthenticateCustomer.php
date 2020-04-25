<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LoginAsCustomer\Model;

use Magento\Customer\Model\Session;
use Magento\LoginAsCustomer\Api\AuthenticateCustomerInterface;

/**
 * @api
 */
class AuthenticateCustomer implements AuthenticateCustomerInterface
{
    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @var \Magento\Checkout\Model\Cart
     */
    private $cart;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $checkoutSession;

    /**
     * AuthenticateCustomer constructor.
     * @param Session $customerSession
     * @param \Magento\Checkout\Model\Cart $cart
     * @param \Magento\Checkout\Model\Session $checkoutSession
     */
    public function __construct(
        Session $customerSession,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Checkout\Model\Session $checkoutSession
    ) {
        $this->customerSession = $customerSession;
        $this->cart = $cart;
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * Authenticate a customer by customer ID
     *
     * @return bool
     * @param int $customerId
     * @param int $adminId
     */
    public function execute(int $customerId, int $adminId):bool
    {
        if ($this->customerSession->getId()) {
            /* Logout if logged in */
            $this->customerSession->logout();
        } else {
            $quote = $this->cart->getQuote();
            /* Remove items from guest cart */
            foreach ($quote->getAllVisibleItems() as $item) {
                $this->cart->removeItem($item->getId());
            }
            $this->cart->save();
        }

        $loggedIn = $this->customerSession->loginById($customerId);
        if ($loggedIn) {
            $this->customerSession->regenerateId();
            $this->customerSession->setLoggedAsCustomerAdmindId($adminId);
        }

        /* Load Customer Quote */
        $this->checkoutSession->loadCustomerQuote();

        $quote = $this->checkoutSession->getQuote();
        $quote->setCustomerIsGuest(0);
        $quote->save();

        return $loggedIn;
    }
}
