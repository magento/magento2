<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AdminAdobeIms\Plugin;

use Magento\AdminAdobeIms\Model\Auth;
use Magento\AdminAdobeIms\Model\ImsConnection;
use Magento\AdminAdobeIms\Service\ImsConfig;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\Exception\AuthorizationException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\User\Model\User;

class ReplaceVerifyIdentityWithImsPlugin
{
    /**
     * @var ImsConfig
     */
    private ImsConfig $adminImsConfig;

    /**
     * @var ImsConnection
     */
    private ImsConnection $adminImsConnection;

    /**
     * @var Auth
     */
    private Auth $auth;

    /**
     * @param ImsConfig $adminImsConfig
     * @param ImsConnection $adminImsConnection
     * @param Auth $auth
     */
    public function __construct(
        ImsConfig $adminImsConfig,
        ImsConnection $adminImsConnection,
        Auth $auth
    ) {
        $this->adminImsConfig = $adminImsConfig;
        $this->adminImsConnection = $adminImsConnection;
        $this->auth = $auth;
    }

    /**
     * Verify if the current user has a valid access_token as we do not ask for a password
     *
     * @param User $subject
     * @param callable $proceed
     * @param string $password
     * @return bool
     * @throws AuthenticationException
     * @throws AuthorizationException
     * @throws NoSuchEntityException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundVerifyIdentity(User $subject, callable $proceed, string $password): bool
    {
        if ($this->adminImsConfig->enabled() !== true) {
            return $proceed($password);
        }

        $valid = $this->verifyImsToken();

        $session = $this->auth->getAuthStorage();
        $session->setAdobeReAuthToken(null);

        if ($valid) {
            return true;
        }

        throw new AuthenticationException(
            __(
                'The account sign-in was incorrect or your account is disabled temporarily. '
                . 'Please wait and try again later.'
            )
        );
    }

    /**
     * Get and verify IMS Token for current user
     *
     * @return bool
     * @throws AuthenticationException
     * @throws AuthorizationException
     * @throws NoSuchEntityException
     */
    private function verifyImsToken(): bool
    {
        $session = $this->auth->getAuthStorage();
        $accessToken = $session->getAdobeAccessToken();
        $reAuthToken = $session->getAdobeReAuthToken();
        if (!$accessToken || !$reAuthToken) {
            throw new AuthenticationException(
                __(
                    'The account sign-in was incorrect or your account is disabled temporarily. '
                    . 'Please wait and try again later.'
                )
            );
        }

        return $this->adminImsConnection->validateToken($reAuthToken);
    }
}
