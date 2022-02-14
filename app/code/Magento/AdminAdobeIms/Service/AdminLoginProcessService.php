<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AdminAdobeIms\Service;

use Magento\AdminAdobeIms\Model\Auth;
use Magento\AdminAdobeIms\Model\User;
use Magento\Framework\Exception\AuthenticationException;

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
     * @param User $adminUser
     */
    public function __construct(
        User $adminUser,
        Auth $auth
    ) {
        $this->adminUser = $adminUser;
        $this->auth = $auth;
    }

    /**
     * @param string $email
     * @throws AuthenticationException
     */
    public function execute($email)
    {
        $adminUser = $this->adminUser->loadByEmail($email);

        if (empty($adminUser['user_id'])) {
            throw new AuthenticationException(__('An authentication error occurred. Verify and try again.'));
        }
        $this->auth->loginByUsername($adminUser['username']);

    }
}