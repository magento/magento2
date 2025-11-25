<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class CheckAndUpdateQtyObserver implements ObserverInterface
{
    /**
     * Check and update the item qty in case of error
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer):void
    {
        $item = $observer->getEvent()->getItem();

        if ($item && $item->getHasError()) {
            $item->setUseOldQty(true);
            $item->getQuote()->setHasError(true);
        }
    }
}
