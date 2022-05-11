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

abstract class AbstractAdminBaseProcessService
{
    /**
     * @var User
     */
    protected User $adminUser;

    /**
     * @var Auth
     */
    protected Auth $auth;

    /**
     * @var DateTime
     */
    protected DateTime $dateTime;

    /**
     * @var LogOut
     */
    private LogOut $logOut;

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
     * Perform login/reauth
     *
     * @param TokenResponseInterface $tokenResponse
     * @param array $profile
     * @return void
     * @throws AdobeImsAuthorizationException
     */
    abstract public function execute(TokenResponseInterface $tokenResponse, array $profile = []): void;

    /**
     * If log in attempt failed, we should clean the Adobe IMS Session
     *
     * @param string $accessToken
     * @return void
     * @throws AdobeImsAuthorizationException
     */
    protected function externalLogout(string $accessToken): void
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
