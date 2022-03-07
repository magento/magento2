<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AdminAdobeIms\Service;

use Magento\AdminAdobeIms\Exception\AdobeImsTokenAuthorizationException;
use Magento\AdminAdobeIms\Model\Auth;
use Magento\AdminAdobeIms\Model\User;
use Magento\AdobeIms\Model\LogIn;
use Magento\AdobeImsApi\Api\Data\TokenResponseInterface;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\Exception\CouldNotSaveException;

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
     * @param User $adminUser
     * @param Auth $auth
     * @param LogIn $logIn
     */
    public function __construct(
        User $adminUser,
        Auth $auth,
        LogIn $logIn
    ) {
        $this->adminUser = $adminUser;
        $this->auth = $auth;
        $this->logIn = $logIn;
    }

    /**
     * Check if user exists and then do the login
     *
     * @param array $profile
     * @param TokenResponseInterface $tokenResponse
     * @return void
     * @throws AdobeImsTokenAuthorizationException
     * @throws CouldNotSaveException|AuthenticationException
     */
    public function execute(array $profile, TokenResponseInterface $tokenResponse): void
    {
        $adminUser = $this->adminUser->loadByEmail($profile['email']);
        if (empty($adminUser['user_id'])) {
            throw new AdobeImsTokenAuthorizationException(
                __('No matching admin user found for Adobe ID.')
            );
        }

        $this->logIn->execute((int)$adminUser['user_id'], $tokenResponse);

        $this->auth->loginByUsername($adminUser['username']);
    }
}
