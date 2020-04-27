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
     * AuthenticateCustomer constructor.
     * @param Session $customerSession
     */
    public function __construct(
        Session $customerSession
    ) {
        $this->customerSession = $customerSession;
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
        }

        $loggedIn = $this->customerSession->loginById($customerId);
        if ($loggedIn) {
            $this->customerSession->regenerateId();
            $this->customerSession->setLoggedAsCustomerAdmindId($adminId);
        }

        return $loggedIn;
    }
}
