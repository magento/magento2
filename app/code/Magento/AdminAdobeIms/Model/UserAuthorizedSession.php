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
    private ImsConnection $imsConnection;

    /**
     * @param Auth $auth
     * @param ImsConnection $imsConnection
     */
    public function __construct(
        Auth $auth,
        ImsConnection $imsConnection
    ) {
        $this->auth = $auth;
        $this->imsConnection = $imsConnection;
    }

    /**
     * @inheritdoc
     */
    public function execute(int $adminUserId = null): bool
    {
        if (!empty($this->auth->getUser()->getId()) && !empty($this->auth->getAuthStorage()->getAdobeAccessToken())) {
            $token = $this->auth->getAuthStorage()->getAdobeAccessToken();

            try {
                $isTokenValid = $this->imsConnection->validateToken($token);
            } catch (AuthorizationException $e) {
                return false;
            }

            if ($isTokenValid) {
                return true;
            }
        }
        return false;
    }
}
