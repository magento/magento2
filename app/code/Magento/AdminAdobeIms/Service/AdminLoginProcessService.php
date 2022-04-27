<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AdminAdobeIms\Service;

use Exception;
use Magento\AdminAdobeIms\Exception\AdobeImsAuthorizationException;
use Magento\AdminAdobeIms\Model\Auth;
use Magento\AdminAdobeIms\Model\LogOut;
use Magento\AdminAdobeIms\Model\User;
use Magento\AdobeImsApi\Api\Data\TokenResponseInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;

class AdminLoginProcessService
{
    /**
     * @var User
     */
    private User $adminUser;

    /**
     * @var Auth
     */
    private Auth $auth;

    /**
     * @var LogOut
     */
    private LogOut $logOut;

    /**
     * @var DateTime
     */
    private DateTime $dateTime;

    /**
     * @param User $adminUser
     * @param Auth $auth
     * @param LogOut $logOut
     * @param DateTime $dateTime
     */
    public function __construct(
        User $adminUser,
        Auth $auth,
        LogOut $logOut,
        DateTime $dateTime
    ) {
        $this->adminUser = $adminUser;
        $this->auth = $auth;
        $this->logOut = $logOut;
        $this->dateTime = $dateTime;
    }

    /**
     * Check if user exists and then do the login
     *
     * @param array $profile
     * @param TokenResponseInterface $tokenResponse
     * @return void
     * @throws AdobeImsAuthorizationException
     */
    public function execute(array $profile, TokenResponseInterface $tokenResponse): void
    {
        try {
            $adminUser = $this->getAdminUser($profile, $tokenResponse);

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
     * Handle Adobe reAuth
     *
     * @param array $profile
     * @param TokenResponseInterface $tokenResponse
     * @return void
     * @throws AdobeImsAuthorizationException
     */
    public function reAuth(array $profile, TokenResponseInterface $tokenResponse): void
    {
        try {
            $adminUser = $this->getAdminUser($profile, $tokenResponse);

            //$this->auth->loginByUsername($adminUser['username']);
            $session = $this->auth->getAuthStorage();
            $session->setAdobeReAuthToken($tokenResponse->getAccessToken());
            $session->setReAuthTokenLastCheckTime($this->dateTime->gmtTimestamp());
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
     * @param TokenResponseInterface $tokenResponse
     * @return array
     * @throws AdobeImsAuthorizationException
     */
    private function getAdminUser(array $profile, TokenResponseInterface $tokenResponse): array
    {
        $adminUser = $this->adminUser->loadByEmail($profile['email']);
        if (empty($adminUser['user_id'])) {
            $this->externalLogout($tokenResponse->getAccessToken());
            throw new AdobeImsAuthorizationException(
                __('No matching admin user found for Adobe ID.')
            );
        }

        return $adminUser;
    }

    /**
     * If log in attempt failed, we should clean the Adobe IMS Session
     *
     * @param string $accessToken
     * @return void
     * @throws AdobeImsAuthorizationException
     */
    private function externalLogout(string $accessToken): void
    {
        try {
            $this->logOut->execute($accessToken);
        } catch (Exception $exception) {
            throw new AdobeImsAuthorizationException(
                __($exception->getMessage())
            );
        }
    }
}
