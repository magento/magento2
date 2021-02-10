<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogRule\Cron;

use Magento\CatalogRule\Model\Indexer\PartialIndex;
use Magento\CatalogRule\Model\Indexer\Rule\RuleProductProcessor;

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
     * @var PartialIndex
     */
    private $partialIndex;

    /**
     * @param RuleProductProcessor $ruleProductProcessor
     * @param PartialIndex $partialIndex
     */
    public function __construct(
        RuleProductProcessor $ruleProductProcessor,
        PartialIndex $partialIndex
    ) {
        $this->ruleProductProcessor = $ruleProductProcessor;
        $this->partialIndex = $partialIndex;
    }

    /**
     * Daily update catalog price rule by cron
     * Update include interval 3 days - current day - 1 days before + 1 days after
     * This method is called from cron process, cron is working in UTC time and
     * we should generate data for interval -1 day ... +1 day
     *
     * @return void
     */
    public function execute()
    {
        $this->ruleProductProcessor->isIndexerScheduled()
            ? $this->partialIndex->partialUpdateCatalogRuleProductPrice()
            : $this->ruleProductProcessor->markIndexerAsInvalid();
    }
}
