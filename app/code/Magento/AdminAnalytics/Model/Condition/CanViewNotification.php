<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AdminAnalytics\Model\Condition;

use Magento\AdminAnalytics\Model\ResourceModel\Viewer\Logger;
use Magento\Framework\View\Layout\Condition\VisibilityConditionInterface;
use Magento\Framework\App\CacheInterface;
use function Magento\PAT\Reports\Utils\readResponseTimeReport;

/**
 * Dynamic validator for UI release notification, manage UI component visibility.
 *
 * Return true if the logged in user has not seen the notification.
 */
class CanViewNotification implements VisibilityConditionInterface
{
    /**
     * Unique condition name.
     *
     * @var string
     */
    private static $conditionName = 'can_view_admin_usage_notification';

    /**
     * Prefix for cache
     *
     * @var string
     */
    private static $cachePrefix = 'admin-usage-notification-popup';

    /**
     * @var Logger
     */
    private $viewerLogger;

    /**
     * @var CacheInterface
     */
    private $cacheStorage;

    /**
     * CanViewNotification constructor.
     *
     * @param Logger         $viewerLogger
     * @param CacheInterface $cacheStorage
     */
    public function __construct(
        Logger $viewerLogger,
        CacheInterface $cacheStorage
    ) {
        $this->viewerLogger = $viewerLogger;
        $this->cacheStorage = $cacheStorage;
    }

    /**
     * Validate if notification popup can be shown and set the notification flag
     *
     * @param      array $arguments Attributes from element node.
     * @inheritdoc
     */
    public function isVisible(array $arguments)
    {
        $cacheKey = self::$cachePrefix;
        $value = $this->cacheStorage->load($cacheKey);
        if ($value !== 'log-exists') {
            $logExists = $this->viewerLogger->checkLogExists();
            if ($logExists) {
                $this->cacheStorage->save('log-exists', $cacheKey);
            }
            return !$logExists;
        }
        return false;
    }

    /**
     * Get condition name
     *
     * @return string
     */
    public function getName()
    {
        return self::$conditionName;
    }
}
