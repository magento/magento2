<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Advertisement\Model\Condition;

use Magento\Backend\Model\Auth\Session;
use Magento\Framework\View\Layout\Condition\VisibilityConditionInterface;
use Magento\Advertisement\Model\AdvertisementFlagManager;

/**
 * Class CanViewNotification
 *
 * Dynamic validator for UI advertisement notification, manage UI component visibility.
 * Return true if the logged in user has not seen the notification.
 */
class CanViewNotification implements VisibilityConditionInterface
{
    /**
     * Unique condition name.
     */
    const NAME = 'can_view_notification';

    /**
     * @var AdvertisementFlagManager
     */
    private $advertisementFlagManager;

    /**
     * @var Session
     */
    private $session;

    /**
     * CanViewNotification constructor.
     *
     * @param AdvertisementFlagManager $advertisementFlagManager
     * @param Session $session
     */
    public function __construct(
        AdvertisementFlagManager $advertisementFlagManager,
        Session $session
    ) {
        $this->advertisementFlagManager = $advertisementFlagManager;
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
        if ($this->advertisementFlagManager->isUserNotified($userId)) {
            return false;
        }

        return $this->advertisementFlagManager->setNotifiedUser($userId);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return self::NAME;
    }
}
