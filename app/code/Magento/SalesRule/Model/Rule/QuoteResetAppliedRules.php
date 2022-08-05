<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesRule\Model\Rule;

use Magento\SalesRule\Model\Spi\QuoteResetAppliedRulesInterface;

/**
 * Reset applied rules to quote
 */
class QuoteResetAppliedRules implements QuoteResetAppliedRulesInterface
{
    /**
     * @inheritDoc
     */
    public function execute(\Magento\Quote\Api\Data\CartInterface $quote): void
    {
        $quote->setCartFixedRules([]);
    }
}
