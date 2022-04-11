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
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\Exception\AuthorizationException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\User\Model\User;

class ReplaceVerifyIdentityWithImsPlugin
{
    /**
     * @var ImsConfig
     */
    private ImsConfig $imsConfig;

    /**
     * @var ImsConnection
     */
    private ImsConnection $imsConnection;

    /**
     * @var Auth
     */
    private Auth $auth;

    /**
     * @var EncryptorInterface
     */
    private EncryptorInterface $encryptor;

    /**
     * @param ImsConfig $imsConfig
     * @param ImsConnection $imsConnection
     * @param Auth $auth
     * @param EncryptorInterface $encryptor
     */
    public function __construct(
        ImsConfig $imsConfig,
        ImsConnection $imsConnection,
        Auth $auth,
        EncryptorInterface $encryptor
    ) {
        $this->imsConfig = $imsConfig;
        $this->imsConnection = $imsConnection;
        $this->auth = $auth;
        $this->encryptor = $encryptor;
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
        if ($this->imsConfig->enabled() !== true) {
            return $proceed($password);
        }

        $valid = $this->verifyImsToken();
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
        if (!$accessToken) {
            throw new AuthenticationException(
                __(
                    'The account sign-in was incorrect or your account is disabled temporarily. '
                    . 'Please wait and try again later.'
                )
            );
        }

        return $this->imsConnection->validateToken($accessToken);
    }
}
