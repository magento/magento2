<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesRule\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\SalesRule\Model\Spi\QuoteResetAppliedRulesInterface;

/**
 * Reset applied rules to quote before collecting totals
 */
class QuoteResetAppliedRulesObserver implements ObserverInterface
{
    /**
     * @param QuoteResetAppliedRulesInterface $resetAppliedRules
     */
    public function __construct(
        private readonly QuoteResetAppliedRulesInterface $resetAppliedRules
    ) {
    }

    /**
     * @inheritDoc
     */
    public function execute(Observer $observer)
    {
        $this->resetAppliedRules->execute($observer->getQuote());
    }
}
