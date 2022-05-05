<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdminAdobeIms\Model;

use Magento\AdminAdobeIms\Service\ImsConfig;
use Magento\AdobeIms\Model\GetAccessToken;
use Magento\AdobeImsApi\Api\GetAccessTokenInterface;

/**
 * Represent the get user access token from session or from db functionality
 */
class GetAccessTokenProxy implements GetAccessTokenInterface
{
    /**
     * @var GetAccessToken
     */
    private GetAccessToken $getAccessTokenFromDb;

    /**
     * @var GetAccessTokenSession
     */
    private GetAccessTokenSession $getAccessTokenFromSession;

    /**
     * @var ImsConfig
     */
    private ImsConfig $adminAdobeImsConfig;

    /**
     * @param GetAccessToken $getAccessTokenFromDb
     * @param GetAccessTokenSession $getAccessTokenFromSession
     * @param ImsConfig $adminAdobeImsConfig
     */
    public function __construct(
        GetAccessToken $getAccessTokenFromDb,
        GetAccessTokenSession $getAccessTokenFromSession,
        ImsConfig $adminAdobeImsConfig
    ) {
        $this->getAccessTokenFromDb = $getAccessTokenFromDb;
        $this->getAccessTokenFromSession = $getAccessTokenFromSession;
        $this->adminAdobeImsConfig = $adminAdobeImsConfig;
    }

    /**
     * @inheritdoc
     */
    public function execute(int $adminUserId = null): ?string
    {
        if ($this->adminAdobeImsConfig->enabled()) {
            return $this->getAccessTokenFromSession->execute($adminUserId);
        }

        return $this->getAccessTokenFromDb->execute($adminUserId);
    }
}
