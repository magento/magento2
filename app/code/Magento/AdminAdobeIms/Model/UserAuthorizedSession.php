<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AdminAdobeIms\Model;

use Magento\AdobeImsApi\Api\UserAuthorizedInterface;
use Magento\Framework\Exception\AuthorizationException;

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
     * @var ImsConnection
     */
    private ImsConnection $adminImsConnection;

    /**
     * @param Auth $auth
     * @param ImsConnection $adminImsConnection
     */
    public function __construct(
        Auth $auth,
        ImsConnection $adminImsConnection
    ) {
        $this->auth = $auth;
        $this->adminImsConnection = $adminImsConnection;
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
            return $this->adminImsConnection->validateToken($token);
        } catch (AuthorizationException $e) {
            return false;
        }
    }
}
