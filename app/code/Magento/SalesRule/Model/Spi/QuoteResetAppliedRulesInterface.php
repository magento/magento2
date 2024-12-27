<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesRule\Model\Spi;

/**
 * Reset applied rules to quote
 * @api
 */
interface QuoteResetAppliedRulesInterface
{
    /**
     * Reset applied rules to quote
     *
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     * @return void
     */
    public function execute(\Magento\Quote\Api\Data\CartInterface $quote): void;
}
