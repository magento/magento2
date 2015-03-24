<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesSequence\Model;

use Magento\Sales\Setup\SalesSetup;
use Magento\SalesSequence\Model\Sequence\SequenceBuilder;

/**
 * Class Observer
 */
class Observer
{
    /**
     * MySql maximal integer for sequences
     */
    const MYSQL_MAX_UNSIGNED_INT = 4294967295;

    /**
     * Sequence warning value
     */
    const SEQUENCE_UNSIGNED_INT_WARNING_VALUE = 4294966295;

    /**
     * @var SequenceBuilder
     */
    private $sequenceBuilder;

    /**
     * @var SalesSetup
     */
    private $salesSetup;

    /**
     * @param SequenceBuilder $sequenceBuilder
     */
    public function __construct(
        SequenceBuilder $sequenceBuilder,
        SalesSetup $salesSetup
    ) {
        $this->sequenceBuilder = $sequenceBuilder;
        $this->salesSetup = $salesSetup;
    }
    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function createSequence($observer)
    {
        $storeId = $observer->getData('store')->getId();
        $defaultEntities = array_keys($this->salesSetup->getDefaultEntities());

        foreach ($defaultEntities as $entityType) {
            $this->sequenceBuilder->setPrefix('')
                ->setSuffix('')
                ->setStartValue(1)
                ->setStoreId($storeId)
                ->setStep(1)
                ->setWarningValue(static::SEQUENCE_UNSIGNED_INT_WARNING_VALUE)
                ->setMaxValue(static::MYSQL_MAX_UNSIGNED_INT)
                ->setEntityType($entityType)->create();
        }
        return $this;
    }
}