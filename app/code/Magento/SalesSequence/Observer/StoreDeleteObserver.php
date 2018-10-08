<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesSequence\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\SalesSequence\Model\Builder;
use Magento\SalesSequence\Model\EntityPool;
use Magento\SalesSequence\Model\Config;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class StoreDeleteObserver
 */
class StoreDeleteObserver implements ObserverInterface
{
    /**
     * @var Builder
     */
    private $sequenceBuilder;

    /**
     * @var EntityPool
     */
    private $entityPool;

    /**
     * @var Config
     */
    private $sequenceConfig;

    /**
     * Initialization
     *
     * @param Builder $sequenceBuilder
     * @param EntityPool $entityPool
     * @param Config $sequenceConfig
     */
    public function __construct(
        Builder $sequenceBuilder,
        EntityPool $entityPool,
        Config $sequenceConfig
    ) {
        $this->sequenceBuilder = $sequenceBuilder;
        $this->entityPool = $entityPool;
        $this->sequenceConfig = $sequenceConfig;
    }

    /**
     * @param EventObserver $observer
     * @return $this
     */
    public function execute(EventObserver $observer)
    {
        $storeId = $observer->getData('store')->getId();
        foreach ($this->entityPool->getEntities() as $entityType) {
            $this->sequenceBuilder->delete($entityType, $storeId);
        }
        return $this;
    }
}
