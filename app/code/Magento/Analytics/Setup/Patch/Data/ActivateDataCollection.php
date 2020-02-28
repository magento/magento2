<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Analytics\Setup\Patch\Data;

use Magento\Analytics\Model\Config\Backend\CollectionTime;
use Magento\Analytics\Model\SubscriptionStatusProvider;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

/**
 * Activate data collection mechanism
 */
class ActivateDataCollection implements DataPatchInterface
{
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var SubscriptionStatusProvider
     */
    private $subscriptionStatusProvider;

    /**
     * @var string
     */
    private $analyticsCollectionTimeConfigPath = 'analytics/general/collection_time';

    /**
     * @var CollectionTime
     */
    private $collectionTimeBackendModel;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param SubscriptionStatusProvider $subscriptionStatusProvider
     * @param CollectionTime $collectionTimeBackandModel
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        SubscriptionStatusProvider $subscriptionStatusProvider,
        CollectionTime $collectionTimeBackandModel
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->subscriptionStatusProvider = $subscriptionStatusProvider;
        $this->collectionTimeBackendModel = $collectionTimeBackandModel;
    }

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function apply()
    {
        $subscriptionStatus = $this->subscriptionStatusProvider->getStatus();
        if ($subscriptionStatus !== $this->subscriptionStatusProvider->getStatusForDisabledSubscription()) {
            $isCollectionProcessActivated = $this->scopeConfig->getValue(CollectionTime::CRON_SCHEDULE_PATH);
            if (!$isCollectionProcessActivated) {
                $this->collectionTimeBackendModel
                    ->setValue($this->scopeConfig->getValue($this->analyticsCollectionTimeConfigPath));
                $this->collectionTimeBackendModel->setPath($this->analyticsCollectionTimeConfigPath);
                $this->collectionTimeBackendModel->afterSave();
            }
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public static function getDependencies()
    {
        return [
            PrepareInitialConfig::class,
        ];
    }
}
