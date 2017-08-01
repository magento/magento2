<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SalesSequence\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\SalesSequence\Model\Builder;
use Magento\SalesSequence\Model\Config as SequenceConfig;
use Magento\SalesSequence\Model\EntityPool;

/**
 * Class InstallData
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 * @codeCoverageIgnore
 * @since 2.0.0
 */
class InstallData implements InstallDataInterface
{
    /**
     * Sales setup factory
     *
     * @var EntityPool
     * @since 2.0.0
     */
    private $entityPool;

    /**
     * @var Builder
     * @since 2.0.0
     */
    private $sequenceBuilder;

    /**
     * @var SequenceConfig
     * @since 2.0.0
     */
    private $sequenceConfig;

    /**
     * @param EntityPool $entityPool
     * @param Builder $sequenceBuilder
     * @param SequenceConfig $sequenceConfig
     * @since 2.0.0
     */
    public function __construct(
        EntityPool $entityPool,
        Builder $sequenceBuilder,
        SequenceConfig $sequenceConfig
    ) {
        $this->entityPool = $entityPool;
        $this->sequenceBuilder = $sequenceBuilder;
        $this->sequenceConfig = $sequenceConfig;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @since 2.0.0
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
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
