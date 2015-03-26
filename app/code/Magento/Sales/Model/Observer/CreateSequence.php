<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Observer;

use Magento\Sales\Setup\SalesSetup;
use Magento\SalesSequence\Model\Config;
use Magento\SalesSequence\Model\Sequence\SequenceBuilder;
use Magento\Framework\Event\Observer;

/**
 * Class CreateSequence
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
     * @var Config
     */
    private $sequenceConfig;

    /**
     * Initialization
     *
     * @param SequenceBuilder $sequenceBuilder
     * @param SalesSetup $salesSetup
     * @param Config $sequenceConfig
     */
    public function __construct(
        SequenceBuilder $sequenceBuilder,
        SalesSetup $salesSetup,
        Config $sequenceConfig
    ) {
        $this->sequenceBuilder = $sequenceBuilder;
        $this->salesSetup = $salesSetup;
        $this->sequenceConfig = $sequenceConfig;
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
            $this->sequenceBuilder->setPrefix($this->sequenceConfig->get('prefix'))
                ->setSuffix($this->sequenceConfig->get('suffix'))
                ->setStartValue($this->sequenceConfig->get('startValue'))
                ->setStoreId($storeId)
                ->setStep($this->sequenceConfig->get('step'))
                ->setWarningValue($this->sequenceConfig->get('warningValue'))
                ->setMaxValue($this->sequenceConfig->get('maxValue'))
                ->setEntityType($entityType)->create();
        }
        return $this;
    }
}
