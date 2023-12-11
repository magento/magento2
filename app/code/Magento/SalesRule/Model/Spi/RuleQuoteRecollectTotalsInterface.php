<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\SalesRule\Model\Spi;

/**
 * Recollect totals for rule related quotes
 * @api
 */
interface RuleQuoteRecollectTotalsInterface
{
    /**
     * Recollect totals for rule related quotes.
     *
     * @param int $ruleId
     * @return void
     */
    public function execute(int $ruleId): void;
}
