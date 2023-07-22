<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesSequence\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\SalesSequence\Model\Builder;
use Magento\SalesSequence\Model\EntityPool;
use Magento\SalesSequence\Model\Config;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class CreateSequence
 */
class SequenceCreatorObserver implements ObserverInterface
{
    /**
     * Initialization
     *
     * @param Builder $sequenceBuilder
     * @param EntityPool $entityPool
     * @param Config $sequenceConfig
     */
    public function __construct(
        private readonly Builder $sequenceBuilder,
        private readonly EntityPool $entityPool,
        private readonly Config $sequenceConfig
    ) {
    }

    /**
     * Observer triggered during adding new store
     *
     * @param EventObserver $observer
     *
     * @return $this|void
     * @throws AlreadyExistsException
     */
    public function execute(EventObserver $observer)
    {
        $storeId = $observer->getData('store')->getId();
        foreach ($this->entityPool->getEntities() as $entityType) {
            $this->sequenceBuilder->setPrefix($storeId)
                ->setSuffix($this->sequenceConfig->get('suffix'))
                ->setStartValue($this->sequenceConfig->get('startValue'))
                ->setStoreId($storeId)
                ->setStep($this->sequenceConfig->get('step'))
                ->setWarningValue($this->sequenceConfig->get('warningValue'))
                ->setMaxValue($this->sequenceConfig->get('maxValue'))
                ->setEntityType($entityType)
                ->create();
        }
        return $this;
    }
}
