<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdminAdobeIms\Service;

use Exception;
use Magento\AdminAdobeIms\Exception\AdobeImsAuthorizationException;
use Magento\AdobeImsApi\Api\Data\TokenResponseInterface;

class AdminLoginProcessService extends AbstractAdminBaseProcessService
{
    /**
     * Check if user exists and then do the login
     *
     * @param TokenResponseInterface $tokenResponse
     * @param array $profile
     * @return void
     * @throws AdobeImsAuthorizationException
     */
    public function execute(TokenResponseInterface $tokenResponse, array $profile = []): void
    {
        try {
            $adminUser = $this->getAdminUser($profile);
            $this->auth->loginByUsername($adminUser['username']);
            $session = $this->auth->getAuthStorage();
            $session->setAdobeAccessToken($tokenResponse->getAccessToken());
            $session->setTokenLastCheckTime($this->dateTime->gmtTimestamp());
        } catch (Exception $exception) {
            $this->externalLogout($tokenResponse->getAccessToken());
            throw new AdobeImsAuthorizationException(
                __($exception->getMessage())
            );
        }
    }

    /**
     * Get Admin User for profile
     *
     * @param array $profile
     * @return array
     * @throws AdobeImsAuthorizationException
     */
    private function getAdminUser(array $profile): array
    {
        $adminUser = $this->adminUser->loadByEmail($profile['email']);
        if (empty($adminUser['user_id'])) {
            throw new AdobeImsAuthorizationException(
                __('No matching admin user found for Adobe ID.')
            );
        }

        return $adminUser;
    }
}
