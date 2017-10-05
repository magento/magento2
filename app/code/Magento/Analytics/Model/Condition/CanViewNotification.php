<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Model\Condition;

use Magento\Framework\View\Layout\Condition\VisibilityConditionInterface;
use Magento\Analytics\Model\NotificationFlag;

/**
 * Class CanViewNotification
 *
 * Dynamic validator for UI analytics notification, manage UI component visibility.
 * Return true if the logged in user has not seen the notification.
 */
class CanViewNotification implements VisibilityConditionInterface
{
    /**
     * Unique condition name.
     */
    const NAME = 'can_view_notification';

    /**
     * @var NotificationFlag
     */
    private $notificationFlag;

    /**
     * CanViewNotification constructor.
     *
     * @param NotificationFlag $notificationFlag
     */
    public function __construct(
        NotificationFlag $notificationFlag
    ) {
        $this->notificationFlag = $notificationFlag;
    }

    /**
     * Validate if notification popup can be shown
     *
     * @inheritdoc
     */
    public function isVisible(array $arguments)
    {
        if ($this->notificationFlag->hasNotificationValueForCurrentUser()) {
            return false;
        }

        return $this->notificationFlag->storeNotificationValueForCurrentUser();
    }

    /**
     * @return string
     */
    public function getName()
    {
        return self::NAME;
    }
}
