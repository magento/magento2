<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Model\Condition;

use Magento\Framework\View\Layout\Condition\VisibilityConditionInterface;
use Magento\Analytics\Model\NotificationTime;

/**
 * Class CanViewNotification
 *
 * Dynamic validator for UI analytics notification, manage UI component visibility.
 * Return true if the logged in user has not seen the notification.
 * @since 2.2.2
 */
class CanViewNotification implements VisibilityConditionInterface
{
    /**
     * Unique condition name.
     */
    const NAME = 'can_view_notification';

    /**
     * @var NotificationTime
     * @since 2.2.2
     */
    private $notificationTime;

    /**
     * CanViewNotification constructor.
     *
     * @param NotificationTime $notificationTime
     * @since 2.2.2
     */
    public function __construct(
        NotificationTime $notificationTime
    ) {
        $this->notificationTime = $notificationTime;
    }

    /**
     * Validate if notification popup can be shown
     *
     * @inheritdoc
     * @since 2.2.2
     */
    public function isVisible(array $arguments)
    {
        $lastNotificationTime = $this->notificationTime->getLastTimeNotificationForCurrentUser();

        if ($lastNotificationTime) {
            return false;
        }

        return $this->notificationTime->storeLastTimeNotificationForCurrentUser();
    }

    /**
     * @return string
     * @since 2.2.2
     */
    public function getName()
    {
        return self::NAME;
    }
}
