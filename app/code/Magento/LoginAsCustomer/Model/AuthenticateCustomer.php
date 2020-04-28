<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LoginAsCustomer\Model;

use Magento\Customer\Model\Session;
use Magento\Framework\Exception\LocalizedException;
use Magento\LoginAsCustomer\Api\AuthenticateCustomerInterface;
use Magento\LoginAsCustomer\Api\Data\AuthenticationDataInterface;

/**
 * @inheritdoc
 */
class AuthenticateCustomer implements AuthenticateCustomerInterface
{
    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @param Session $customerSession
     */
    public function __construct(
        Session $customerSession
    ) {
        $this->customerSession = $customerSession;
    }

    /**
     * @inheritdoc
     */
    public function execute(AuthenticationDataInterface $authenticationData): void
    {
        if ($this->customerSession->getId()) {
            $this->customerSession->logout();
        }

        $loggedIn = $this->customerSession->loginById($authenticationData->getCustomerId());
        if (!$loggedIn) {
            throw new LocalizedException(__('Login was not successful.'));
        }

        $this->customerSession->regenerateId();
        $this->customerSession->setLoggedAsCustomerAdmindId($authenticationData->getAdminId());
    }
}
