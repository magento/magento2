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
<<<<<<< HEAD
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Config\DataInterfaceFactory;

/**
 * Class CanViewNotification
 *
=======

/**
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
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
<<<<<<< HEAD
     * @var DataInterfaceFactory
     */
    private $configFactory;

    /**
=======
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
     * CanViewNotification constructor.
     *
     * @param Logger $viewerLogger
     * @param Session $session
     * @param ProductMetadataInterface $productMetadata
     * @param CacheInterface $cacheStorage
<<<<<<< HEAD
     * @param DataInterfaceFactory|null $configFactory
=======
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
     */
    public function __construct(
        Logger $viewerLogger,
        Session $session,
        ProductMetadataInterface $productMetadata,
<<<<<<< HEAD
        CacheInterface $cacheStorage,
        DataInterfaceFactory $configFactory = null
=======
        CacheInterface $cacheStorage
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
    ) {
        $this->viewerLogger = $viewerLogger;
        $this->session = $session;
        $this->productMetadata = $productMetadata;
        $this->cacheStorage = $cacheStorage;
<<<<<<< HEAD
        $this->configFactory = $configFactory ?? ObjectManager::getInstance()->get(DataInterfaceFactory::class);
=======
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
    }

    /**
     * Validate if notification popup can be shown and set the notification flag
     *
     * @inheritdoc
     */
    public function isVisible(array $arguments)
    {
<<<<<<< HEAD
        $config = $this->configFactory->create(['componentName' => 'release_notification']);
        $releaseContentVerion = $config->get('release_notification/arguments/data/releaseContentVersion');
=======
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
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
<<<<<<< HEAD
        if ($value) {
            $value = version_compare(
                $this->productMetadata->getVersion(),
                $releaseContentVerion,
                '<='
            );
        }
        
=======
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
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
