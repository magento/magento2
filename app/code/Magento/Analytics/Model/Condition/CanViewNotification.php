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
 * @since 2.2.0
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
     * @since 2.2.0
     */
    private $notificationInterval = 604800;

    /**
     * @var NotificationTime
     * @since 2.2.0
     */
    private $notificationTime;

    /**
     * @var DateTimeFactory
     * @since 2.2.0
     */
    private $dateTimeFactory;

    /**
     * CanViewNotification constructor.
     *
     * @param NotificationTime $notificationTime
     * @param DateTimeFactory $dateTimeFactory
     * @since 2.2.0
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
     * @since 2.2.0
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
     * @since 2.2.0
     */
    public function getName()
    {
        return self::NAME;
    }
}
