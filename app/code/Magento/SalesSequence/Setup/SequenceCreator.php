<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SalesSequence\Setup;

use Magento\Framework\Exception\AlreadyExistsException;
use Magento\SalesSequence\Model\Builder;
use Magento\SalesSequence\Model\Config as SequenceConfig;
use Magento\SalesSequence\Model\EntityPool;

/**
 * Initial creating sequences.
 */
class SequenceCreator
{
    /**
     * @param EntityPool $entityPool Sales setup factory
     * @param Builder $sequenceBuilder
     * @param SequenceConfig $sequenceConfig
     */
    public function __construct(
        private readonly EntityPool $entityPool,
        private readonly Builder $sequenceBuilder,
        private readonly SequenceConfig $sequenceConfig
    ) {
    }

    /**
     * Creates sales sequences.
     *
     * @throws AlreadyExistsException
     */
    public function create()
    {
        $defaultStoreIds = [0, 1];
        foreach ($defaultStoreIds as $storeId) {
            foreach ($this->entityPool->getEntities() as $entityType) {
                $this->sequenceBuilder->setPrefix($this->sequenceConfig->get('prefix'))
                    ->setSuffix($this->sequenceConfig->get('suffix'))
                    ->setStartValue($this->sequenceConfig->get('startValue'))
                    ->setStoreId($storeId)
                    ->setStep($this->sequenceConfig->get('step'))
                    ->setWarningValue($this->sequenceConfig->get('warningValue'))
                    ->setMaxValue($this->sequenceConfig->get('maxValue'))
                    ->setEntityType($entityType)->create();
            }
        }
    }
}
