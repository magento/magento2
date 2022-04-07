<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AdminAdobeIms\Service;

use Exception;
use Magento\AdminAdobeIms\Exception\AdobeImsTokenAuthorizationException;
use Magento\AdminAdobeIms\Model\Auth;
use Magento\AdminAdobeIms\Model\LogOut;
use Magento\AdminAdobeIms\Model\User;
use Magento\AdobeIms\Model\LogIn;
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
     * @var LogIn
     */
    private LogIn $logIn;

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
     * @param LogIn $logIn
     * @param LogOut $logOut
     * @param DateTime $dateTime
     */
    public function __construct(
        User $adminUser,
        Auth $auth,
        LogIn $logIn,
        LogOut $logOut,
        DateTime $dateTime
    ) {
        $this->adminUser = $adminUser;
        $this->auth = $auth;
        $this->logIn = $logIn;
        $this->logOut = $logOut;
        $this->dateTime = $dateTime;
    }

    /**
     * Check if user exists and then do the login
     *
     * @param array $profile
     * @param TokenResponseInterface $tokenResponse
     * @return void
     * @throws AdobeImsTokenAuthorizationException
     */
    public function execute(array $profile, TokenResponseInterface $tokenResponse): void
    {
        $adminUser = $this->adminUser->loadByEmail($profile['email']);
        if (empty($adminUser['user_id'])) {
            $this->externalLogout($tokenResponse->getAccessToken());
            throw new AdobeImsTokenAuthorizationException(
                __('No matching admin user found for Adobe ID.')
            );
        }

        try {
            $this->logIn->execute((int)$adminUser['user_id'], $tokenResponse);
            $this->auth->loginByUsername($adminUser['username']);
            $session = $this->auth->getAuthStorage();
            $session->setAdobeAccessToken($tokenResponse->getAccessToken());
            $session->setTokenLastCheckTime($this->dateTime->gmtTimestamp());
        } catch (Exception $exception) {
            $this->externalLogout($tokenResponse->getAccessToken());
            throw new AdobeImsTokenAuthorizationException(
                __($exception->getMessage())
            );
        }
    }

    /**
     * If log in attempt failed, we should clean the Adobe IMS Session
     *
     * @param string $accessToken
     * @return void
     * @throws AdobeImsTokenAuthorizationException
     */
    private function externalLogout(string $accessToken): void
    {
        try {
            $this->logOut->execute($accessToken);
        } catch (Exception $exception) {
            throw new AdobeImsTokenAuthorizationException(
                __($exception->getMessage())
            );
        }
    }
}
