<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LoginAsCustomer\Model;

use Magento\Customer\Model\Session;
use Magento\Framework\Exception\LocalizedException;
use Magento\LoginAsCustomerApi\Api\AuthenticateCustomerBySecretInterface;
use Magento\LoginAsCustomerApi\Api\GetAuthenticationDataBySecretInterface;

/**
 * @inheritdoc
 *
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class AuthenticateCustomerBySecret implements AuthenticateCustomerBySecretInterface
{
    /**
     * @var GetAuthenticationDataBySecretInterface
     */
    private $getAuthenticationDataBySecret;

    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @param GetAuthenticationDataBySecretInterface $getAuthenticationDataBySecret
     * @param Session $customerSession
     */
    public function __construct(
        GetAuthenticationDataBySecretInterface $getAuthenticationDataBySecret,
        Session $customerSession
    ) {
        $this->getAuthenticationDataBySecret = $getAuthenticationDataBySecret;
        $this->customerSession = $customerSession;
    }

    /**
     * @inheritdoc
     */
    public function execute(string $secret): void
    {
        $authenticationData = $this->getAuthenticationDataBySecret->execute($secret);

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
