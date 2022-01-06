<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Multishipping\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Multishipping\Model\DisableMultishipping;
use Magento\Quote\Api\Data\CartInterface;

/**
 * Observer for disabling Multishipping mode.
 */
class DisableMultishippingObserver implements ObserverInterface
{
    /**
     * @var DisableMultishipping
     */
    private $disableMultishipping;

    /**
     * @param DisableMultishipping $disableMultishipping
     */
    public function __construct(
        DisableMultishipping $disableMultishipping
    ) {
        $this->disableMultishipping = $disableMultishipping;
    }

    /**
     * Disable Multishipping mode before saving Quote.
     *
     * @param EventObserver $observer
     * @return void
     */
    public function execute(EventObserver $observer): void
    {
        /** @var CartInterface $quote */
        $quote = $observer->getEvent()->getCart()->getQuote();
        $this->disableMultishipping->execute($quote);
    }
}
