<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Model\Condition;

use Magento\Framework\View\Layout\Condition\VisibilityConditionInterface;
use Magento\Analytics\Model\NotificationTime;
use Magento\Framework\Intl\DateTimeFactory;

/**
 * Class CanViewNotification
 *
 * Dynamic validator for UI signUp notification form, manage Ui component visibility.
 * Return true if last notification was shipped seven days ago.
 */
class CanViewNotification implements VisibilityConditionInterface
{
    /**
     * Unique condition name.
     */
    const NAME = 'can_view_notification';

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
     * @inheritdoc
     */
    public function isVisible(array $arguments)
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

    /**
     * @return string
     */
    public function getName()
    {
        return self::NAME;
    }
}
