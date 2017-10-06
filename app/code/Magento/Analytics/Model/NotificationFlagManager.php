<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Model;

use Magento\Framework\FlagManager;

/**
 * Class NotificationFlagManager
 *
 * Manage access to notification flag
 *
 */
class NotificationFlagManager
{
    const NOTIFICATION_SEEN = 'analytics_notification_seen_admin_';

    /**
     * @var FlagManager
     */
    private $flagManager;

    /**
     * NotificationFlagManager constructor.
     *
     * @param FlagManager $flagManager
     */
    public function __construct(
        FlagManager $flagManager
    ) {
        $this->flagManager = $flagManager;
    }

    /**
     * Sets the flag to indicate the user was notified about Analytic services
     * @param $userId
     * @return bool
     */
    public function setNotifiedUser($userId)
    {
        $flagCode = self::NOTIFICATION_SEEN . $userId;
        return $this->flagManager->saveFlag($flagCode, 1);
    }

    /**
     * Returns the flag data if the user was notified about Analytic services
     * @param $userId
     * @return bool
     */
    public function isUserNotified($userId)
    {
        return $this->flagManager->getFlagData(self::NOTIFICATION_SEEN . $userId);
    }
}
