<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Observer;

use Magento\Sales\Setup\SalesSetup;
use Magento\SalesSequence\Model\Sequence\SequenceBuilder;
use Magento\Framework\Event\Observer;

/**
 * Class Observer
 */
class CreateSequence
{
    /**
     * @var SequenceBuilder
     */
    private $sequenceBuilder;

    /**
     * @var SalesSetup
     */
    private $salesSetup;

    /**
     * Initialization
     *
     * @param SequenceBuilder $sequenceBuilder
     * @param SalesSetup $salesSetup
     */
    public function __construct(
        SequenceBuilder $sequenceBuilder,
        SalesSetup $salesSetup
    ) {
        $this->sequenceBuilder = $sequenceBuilder;
        $this->salesSetup = $salesSetup;
    }
    /**
     * @param Observer $observer
     * @return $this
     */
    public function execute(Observer $observer)
    {
        $storeId = $observer->getData('store')->getId();
        $defaultEntities = array_keys($this->salesSetup->getDefaultEntities());
        foreach ($defaultEntities as $entityType) {
            $this->sequenceBuilder->setPrefix('')
                ->setSuffix('')
                ->setStartValue(1)
                ->setStoreId($storeId)
                ->setStep(1)
                ->setWarningValue(SequenceBuilder::SEQUENCE_UNSIGNED_INT_WARNING_VALUE)
                ->setMaxValue(SequenceBuilder::MYSQL_MAX_UNSIGNED_INT)
                ->setEntityType($entityType)->create();
        }
        return $this;
    }
}
