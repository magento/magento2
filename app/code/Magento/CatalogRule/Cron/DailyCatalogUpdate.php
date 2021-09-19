<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogRule\Cron;

use Magento\CatalogRule\Model\Indexer\Rule\RuleProductProcessor;
use Magento\CatalogRule\Model\ResourceModel\Rule\CollectionFactory as RuleCollectionFactory;

/**
 * Daily update catalog price rule by cron
 */
class DailyCatalogUpdate
{
    /**
     * @var RuleProductProcessor
     */
    protected $ruleProductProcessor;

    /**
     * @var RuleCollectionFactory
     */
    private $ruleCollectionFactory;

    /**
     * @param RuleProductProcessor $ruleProductProcessor
     * @param RuleCollectionFactory $ruleCollectionFactory
     */
    public function __construct(
        RuleProductProcessor $ruleProductProcessor,
        RuleCollectionFactory $ruleCollectionFactory
    ) {
        $this->ruleProductProcessor = $ruleProductProcessor;
        $this->ruleCollectionFactory = $ruleCollectionFactory;
    }

    /**
     * Daily update catalog price rule by cron
     *
     * Update include interval 3 days - current day - 1 days before + 1 days after
     * This method is called from cron process, cron is working in UTC time and
     * we should generate data for interval -1 day ... +1 day
     *
     * @return void
     */
    public function execute()
    {
        $ruleCollection = $this->ruleCollectionFactory->create();
        $ruleCollection->addIsActiveFilter();
        if ($ruleCollection->getSize()) {
            $this->ruleProductProcessor->markIndexerAsInvalid();
        }
    }
}
