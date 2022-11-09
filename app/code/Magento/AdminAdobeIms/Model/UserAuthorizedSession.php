<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdminAdobeIms\Model;

use Magento\AdobeImsApi\Api\UserAuthorizedInterface;
use Magento\Framework\Exception\AuthorizationException;
use Magento\AdobeImsApi\Api\IsTokenValidInterface;

/**
 * Represent functionality for getting information from session if user is authorised or not
 */
class UserAuthorizedSession implements UserAuthorizedInterface
{
    /**
     * @var Auth
     */
    private Auth $auth;

    /**
     * @var IsTokenValidInterface
     */
    private IsTokenValidInterface $isTokenValid;

    /**
     * @param Auth $auth
     * @param IsTokenValidInterface $isTokenValid
     */
    public function __construct(
        Auth $auth,
        IsTokenValidInterface $isTokenValid
    ) {
        $this->auth = $auth;
        $this->isTokenValid = $isTokenValid;
    }

    /**
     * @inheritdoc
     */
    public function execute(int $adminUserId = null): bool
    {
        $token = $this->auth->getAuthStorage()->getAdobeAccessToken();

        if (empty($token) || empty($this->auth->getUser()->getId())) {
            return false;
        }

        try {
            return $this->isTokenValid->validateToken($token);
        } catch (AuthorizationException $e) {
            return false;
        }
    }
}
