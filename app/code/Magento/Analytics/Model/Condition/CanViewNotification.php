<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Model\Condition;

use Magento\Backend\Model\Auth\Session;
use Magento\Framework\App\ProductMetadataInterface;
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
     * Magento Version to only show Advertisement module notification content and hide Analytics notification
     */
    const VERSION_TO_HIDE = '2.2.1-dev';

    /**
     * @var NotificationFlagManager
     */
    private $notificationFlagManager;

    /**
     * @var Session
     */
    private $session;

    /**
     * @var ProductMetadataInterface
     */
    private $productMetadataInterface;

    /**
     * CanViewNotification constructor.
     *
     * @param NotificationFlagManager $notificationFlagManager
     * @param Session $session
     * @param ProductMetadataInterface $productMetadataInterface
     */
    public function __construct(
        NotificationFlagManager $notificationFlagManager,
        Session $session,
        ProductMetadataInterface $productMetadataInterface
    ) {
        $this->notificationFlagManager = $notificationFlagManager;
        $this->session = $session;
        $this->productMetadataInterface = $productMetadataInterface;
    }

    /**
     * Validate if notification popup can be shown and set the notification flag
     *
     * @inheritdoc
     */
    public function isVisible(array $arguments)
    {
        $version = $this->productMetadataInterface->getVersion();

        $userId = $this->session->getUser()->getId();
        if (!strcmp($version, self::VERSION_TO_HIDE) || $this->notificationFlagManager->isUserNotified($userId)) {
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
