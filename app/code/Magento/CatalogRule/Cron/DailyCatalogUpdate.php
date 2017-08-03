<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogRule\Cron;

/**
 * Class \Magento\CatalogRule\Cron\DailyCatalogUpdate
 *
 * @since 2.0.0
 */
class DailyCatalogUpdate
{
    /**
     * @var \Magento\CatalogRule\Model\Indexer\Rule\RuleProductProcessor
     * @since 2.0.0
     */
    protected $ruleProductProcessor;

    /**
     * @param \Magento\CatalogRule\Model\Indexer\Rule\RuleProductProcessor $ruleProductProcessor
     * @since 2.0.0
     */
    public function __construct(\Magento\CatalogRule\Model\Indexer\Rule\RuleProductProcessor $ruleProductProcessor)
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
     * @since 2.0.0
     */
    public function execute()
    {
        $this->ruleProductProcessor->markIndexerAsInvalid();
    }
}
