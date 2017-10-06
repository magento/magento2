<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Model\Condition;

use Magento\Backend\Model\Auth\Session;
use Magento\Framework\View\Layout\Condition\VisibilityConditionInterface;
use Magento\Analytics\Model\NotificationFlagManager;

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
     * @var NotificationFlagManager
     */
    private $notificationFlagManager;

    /**
     * @var Session
     */
    private $session;

    /**
     * CanViewNotification constructor.
     *
     * @param NotificationFlagManager $notificationFlagManager
     * @param Session $session
     */
    public function __construct(
        NotificationFlagManager $notificationFlagManager,
        Session $session
    ) {
        $this->notificationFlagManager = $notificationFlagManager;
        $this->session = $session;
    }

    /**
     * Validate if notification popup can be shown and set the notification flag
     *
     * @inheritdoc
     */
    public function isVisible(array $arguments)
    {
        $userId = $this->session->getUser()->getId();
        if ($this->notificationFlagManager->isUserNotified($userId)) {
            return false;
        }

        return $this->notificationFlagManager->setNotifiedUser($userId);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return self::NAME;
    }
}
