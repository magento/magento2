<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ReleaseNotification\Model\Condition;

use Magento\ReleaseNotification\Model\ResourceModel\Viewer\Logger;
use Magento\Backend\Model\Auth\Session;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\View\Layout\Condition\VisibilityConditionInterface;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Config\DataInterfaceFactory;

/**
 * Class CanViewNotification
 *
 * Dynamic validator for UI release notification, manage UI component visibility.
 * Return true if the logged in user has not seen the notification.
 */
class CanViewNotification implements VisibilityConditionInterface
{
    /**
     * Unique condition name.
     *
     * @var string
     */
    private static $conditionName = 'can_view_notification';

    /**
     * Prefix for cache
     *
     * @var string
     */
    private static $cachePrefix = 'release-notification-popup-';

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
     * @var CacheInterface
     */
    private $cacheStorage;

    /**
     * @var DataInterfaceFactory
     */
    private $configFactory;

    /**
     * CanViewNotification constructor.
     *
     * @param Logger $viewerLogger
     * @param Session $session
     * @param ProductMetadataInterface $productMetadata
     * @param CacheInterface $cacheStorage
     * @param DataInterfaceFactory|null $configFactory
     */
    public function __construct(
        Logger $viewerLogger,
        Session $session,
        ProductMetadataInterface $productMetadata,
        CacheInterface $cacheStorage,
        DataInterfaceFactory $configFactory = null
    ) {
        $this->viewerLogger = $viewerLogger;
        $this->session = $session;
        $this->productMetadata = $productMetadata;
        $this->cacheStorage = $cacheStorage;
        $this->configFactory = $configFactory ?? ObjectManager::getInstance()->get(DataInterfaceFactory::class);
    }

    /**
     * Validate if notification popup can be shown and set the notification flag
     *
     * @inheritdoc
     */
    public function isVisible(array $arguments)
    {
        $config = $this->configFactory->create(['componentName' => 'release_notification']);
        $releaseContentVerion = $config->get('release_notification/arguments/data/releaseContentVersion');
        $userId = $this->session->getUser()->getId();
        $cacheKey = self::$cachePrefix . $userId;
        $value = $this->cacheStorage->load($cacheKey);
        if ($value === false) {
            $value = version_compare(
                $this->viewerLogger->get($userId)->getLastViewVersion(),
                $this->productMetadata->getVersion(),
                '<'
            );
            $this->cacheStorage->save(false, $cacheKey);
        }
        if ($value) {
            $value = version_compare(
                $this->productMetadata->getVersion(),
                $releaseContentVerion,
                '<='
            );
        }
        
        return (bool)$value;
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
