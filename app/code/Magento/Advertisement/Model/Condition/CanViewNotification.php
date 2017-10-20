<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Advertisement\Model\Condition;

use Magento\Advertisement\Model\ResourceModel\Viewer\Logger;
use Magento\Backend\Model\Auth\Session;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\View\Layout\Condition\VisibilityConditionInterface;

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
     * @var Logger
     */
    private $viewerLogger;

    /**
     * @var Session
     */
    private $session;

    /**
     * @var ProductMetadataInterface
     */
    private $productMetadata;

    /**
     * CanViewNotification constructor.
     *
     * @param Logger $viewerLogger
     * @param Session $session
     */
    public function __construct(
        Logger $viewerLogger,
        Session $session,
        ProductMetadataInterface $productMetadata
    ) {
        $this->viewerLogger = $viewerLogger;
        $this->session = $session;
        $this->productMetadata = $productMetadata;
    }

    /**
     * Validate if notification popup can be shown and set the notification flag
     *
     * @inheritdoc
     */
    public function isVisible(array $arguments)
    {
        $userId = $this->session->getUser()->getId();
        $viewerLog = $this->viewerLogger->get($userId);
        $version = $this->productMetadata->getVersion();
        if ($viewerLog == null
            || $viewerLog->getLastViewVersion() == null
            || $viewerLog->getLastViewVersion() < $version
        ) {
            $this->viewerLogger->log($userId, $version);
            return true;
        } else {
            return false;
        }
    }

    /**
     * @return string
     */
    public function getName()
    {
        return self::NAME;
    }
}
