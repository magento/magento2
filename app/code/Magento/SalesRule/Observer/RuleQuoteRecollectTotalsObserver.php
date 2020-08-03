<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\SalesRule\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\SalesRule\Model\Rule;
use Magento\SalesRule\Model\Spi\RuleQuoteRecollectTotalsInterface;

/**
 * Forces related quotes to be recollected for inactive rule.
 */
class RuleQuoteRecollectTotalsObserver implements ObserverInterface
{
    /**
     * @var RuleQuoteRecollectTotalsInterface
     */
    private $recollectTotals;

    /**
     * Initializes dependencies
     *
     * @param RuleQuoteRecollectTotalsInterface $recollectTotals
     */
    public function __construct(RuleQuoteRecollectTotalsInterface $recollectTotals)
    {
        $this->recollectTotals = $recollectTotals;
    }

    /**
     * Forces related quotes to be recollected, if the rule was disabled or deleted.
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer): void
    {
        /** @var Rule $rule */
        $rule = $observer->getRule();
        if (!$rule->isObjectNew() && (!$rule->getIsActive() || $rule->isDeleted())) {
            $this->recollectTotals->execute((int) $rule->getId());
        }
    }
}
