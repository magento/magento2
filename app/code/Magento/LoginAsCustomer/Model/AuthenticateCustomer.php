<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LoginAsCustomer\Model;

use Magento\Customer\Model\Session;
use Magento\Framework\Exception\LocalizedException;
use Magento\LoginAsCustomerApi\Api\AuthenticateCustomerInterface;
use Magento\LoginAsCustomerApi\Api\Data\AuthenticationDataInterface;

/**
 * @inheritdoc
 *
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
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

        $result = $this->customerSession->loginById($authenticationData->getCustomerId());
        if (false === $result) {
            throw new LocalizedException(__('Login was not successful.'));
        }

        $this->customerSession->regenerateId();
        $this->customerSession->setLoggedAsCustomerAdmindId($authenticationData->getAdminId());
    }
}
