<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogRule\Model;

class Cron
{
    /**
     * @var Indexer\Rule\RuleProductProcessor
     */
    protected $ruleProductProcessor;

    /**
     * @param Indexer\Rule\RuleProductProcessor $ruleProductProcessor
     */
    public function __construct(Indexer\Rule\RuleProductProcessor $ruleProductProcessor)
    {
        $this->ruleProductProcessor = $ruleProductProcessor;
    }

    /**
     * Daily update catalog price rule by cron
     * Update include interval 3 days - current day - 1 days before + 1 days after
     * This method is called from cron process, cron is working in UTC time and
     * we should generate data for interval -1 day ... +1 day
     *
     * @return void
     */
    public function dailyCatalogUpdate()
    {
        $this->ruleProductProcessor->markIndexerAsInvalid();
    }
}
