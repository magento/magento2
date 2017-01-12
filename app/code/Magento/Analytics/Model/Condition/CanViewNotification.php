<?php
/**
 * Copyright © 2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Analytics\Model\Condition;

use Magento\Backend\Model\View\Layout\ConditionInterface;
use Magento\Analytics\Model\NotificationTime;
use Magento\Framework\Intl\DateTimeFactory;

/**
 * Class CanViewNotification
 */
class CanViewNotification implements ConditionInterface
{
    /**
     * Time interval in seconds
     *
     * @var int
     */
    private $notificationInterval = 604800;

    /**
     * @var NotificationTime
     */
    private $notificationTime;

    /**
     * @var DateTimeFactory
     */
    private $dateTimeFactory;

    /**
     * CanViewNotification constructor.
     *
     * @param NotificationTime $notificationTime
     * @param DateTimeFactory $dateTimeFactory
     */
    public function __construct(
        NotificationTime $notificationTime,
        DateTimeFactory $dateTimeFactory
    ) {
        $this->notificationTime = $notificationTime;
        $this->dateTimeFactory = $dateTimeFactory;
    }

    /**
     * Validate is notification popup can be shown
     *
     * @return bool
     */
    public function validate()
    {
        $lastNotificationTime = $this->notificationTime->getLastTimeNotification();
        if (!$lastNotificationTime) {
            return false;
        }
        $datetime = $this->dateTimeFactory->create();
        return (
            $datetime->getTimestamp() >= $lastNotificationTime + $this->notificationInterval
        );
    }
}
