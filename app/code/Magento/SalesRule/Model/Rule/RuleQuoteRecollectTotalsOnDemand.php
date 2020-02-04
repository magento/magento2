<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\SalesRule\Model\Rule;

use Magento\Quote\Model\ResourceModel\Quote;
use Magento\SalesRule\Model\Spi\RuleQuoteRecollectTotalsInterface;

/**
 * Forces related quotes to be recollected on demand.
 */
class RuleQuoteRecollectTotalsOnDemand implements RuleQuoteRecollectTotalsInterface
{
    /**
     * @var Quote
     */
    private $quoteResourceModel;

    /**
     * Initializes dependencies
     *
     * @param Quote $quoteResourceModel
     */
    public function __construct(Quote $quoteResourceModel)
    {
        $this->quoteResourceModel = $quoteResourceModel;
    }

    /**
     * Set "trigger_recollect" flag for active quotes which the given rule is applied to.
     *
     * @param int $ruleId
     * @return void
     */
    public function execute(int $ruleId): void
    {
        $this->quoteResourceModel->getConnection()
            ->update(
                $this->quoteResourceModel->getMainTable(),
                ['trigger_recollect' => 1],
                [
                    'is_active = ?' => 1,
                    'FIND_IN_SET(?, applied_rule_ids)' => $ruleId
                ]
            );
    }
}
