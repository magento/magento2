<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Model;

use Magento\Framework\FlagManager;

/**
 * Class NotificationTime
 *
 * Manage access to notification time flag
 *
 */
class NotificationTime
{
    const NOTIFICATION_TIME = 'notification_time';

    /**
     * @var FlagManager
     */
    private $flagManager;

    /**
     * NotificationTime constructor.
     *
     * @param FlagManager $flagManager
     */
    public function __construct(
        FlagManager $flagManager
    ) {
        $this->flagManager = $flagManager;
    }

    /**
     * Stores last notification time
     *
     * @param string $value
     * @return bool
     */
    public function storeLastTimeNotification($value)
    {
        return $this->flagManager->saveFlag(self::NOTIFICATION_TIME, $value);
    }

    /**
     * Returns last time when merchant was notified about Analytic services
     *
     * @return int
     */
    public function getLastTimeNotification()
    {
        return $this->flagManager->getFlagData(self::NOTIFICATION_TIME);
    }

    /**
     * Remove last notification time flag.
     *
     * @return bool
     */
    public function unsetLastTimeNotificationValue()
    {
        return $this->flagManager->deleteFlag(self::NOTIFICATION_TIME);
    }
}
