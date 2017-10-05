<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Model;

use Magento\Backend\Model\Auth\Session;
use Magento\Framework\FlagManager;

/**
 * Class NotificationFlag
 *
 * Manage access to notification time flag
 *
 */
class NotificationFlag
{
    const NOTIFICATION_SEEN = 'analytics_notification_seen_admin_';

    /**
     * @var FlagManager
     */
    private $flagManager;

    /**
     * @var Session
     */
    private $session;

    /**
     * NotificationFlag constructor.
     *
     * @param FlagManager $flagManager
     * @param Session $session
     */
    public function __construct(
        FlagManager $flagManager,
        Session $session
    ) {
        $this->flagManager = $flagManager;
        $this->session = $session;
    }

    /**
     * Stores flag to indicate the user was notified about Analytic services
     *
     * @return bool
     */
    public function storeNotificationValueForCurrentUser()
    {
        $flagCode = self::NOTIFICATION_SEEN . $this->session->getUser()->getId();
        return $this->flagManager->saveFlag($flagCode, 1);
    }

    /**
     * Returns the flag data if the user was notified about Analytic services
     *
     * @return bool
     */
    public function hasNotificationValueForCurrentUser()
    {
        return $this->flagManager->getFlagData(self::NOTIFICATION_SEEN . $this->session->getUser()->getId());
    }

    /**
     * Remove the notification seen flag
     *
     * @return bool
     */
    public function unsetNotificationValueForCurrentUser()
    {
        return $this->flagManager->deleteFlag(self::NOTIFICATION_SEEN . $this->session->getUser()->getId());
    }
}
