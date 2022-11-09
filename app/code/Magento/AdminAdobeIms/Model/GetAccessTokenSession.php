<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdminAdobeIms\Model;

use Magento\AdobeImsApi\Api\GetAccessTokenInterface;

/**
 * Represents get user access token from session functionality
 */
class GetAccessTokenSession implements GetAccessTokenInterface
{
    /**
     * @var Auth
     */
    private Auth $auth;

    /**
     * @param Auth $auth
     */
    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
    }

    /**
     * @inheritdoc
     */
    public function execute(int $adminUserId = null): ?string
    {
        return $this->auth->getAuthStorage()->getAdobeAccessToken() ?? null;
    }
}
