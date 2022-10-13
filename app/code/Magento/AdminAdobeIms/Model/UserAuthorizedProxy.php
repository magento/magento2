<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdminAdobeIms\Model;

use Magento\AdminAdobeIms\Service\ImsConfig;
use Magento\AdobeIms\Model\UserAuthorized;
use Magento\AdobeImsApi\Api\UserAuthorizedInterface;

/**
 * Represents functionality for getting information from session or from db about if user is authorised or not
 */
class UserAuthorizedProxy implements UserAuthorizedInterface
{
    /**
     * @var UserAuthorized
     */
    private UserAuthorized $userAuthorizedDb;

    /**
     * @var UserAuthorizedSession
     */
    private UserAuthorizedSession $userAuthorizedSession;

    /**
     * @var ImsConfig
     */
    private ImsConfig $adminAdobeImsConfig;

    /**
     * @param UserAuthorized $userAuthorizedDb
     * @param UserAuthorizedSession $userAuthorizedSession
     * @param ImsConfig $adminAdobeImsConfig
     */
    public function __construct(
        UserAuthorized $userAuthorizedDb,
        UserAuthorizedSession $userAuthorizedSession,
        ImsConfig $adminAdobeImsConfig
    ) {
        $this->userAuthorizedDb = $userAuthorizedDb;
        $this->userAuthorizedSession = $userAuthorizedSession;
        $this->adminAdobeImsConfig = $adminAdobeImsConfig;
    }

    /**
     * @inheritdoc
     */
    public function execute(int $adminUserId = null): bool
    {
        if ($this->adminAdobeImsConfig->enabled()) {
            return $this->userAuthorizedSession->execute($adminUserId);
        }

        return $this->userAuthorizedDb->execute($adminUserId);
    }
}
