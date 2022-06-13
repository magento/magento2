<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Model\Quote;

use Magento\Quote\Model\Quote;

class QuantityCollector
{
    /**
     * Collect items qty
     *
     * @param Quote $quote
     * @return Quote
     */
    public function collectItemsQtys(Quote $quote)
    {
        $quoteItems = $quote->getAllVisibleItems();
        $quote->setItemsCount(0);
        $quote->setItemsQty(0);
        $quote->setVirtualItemsQty(0);

        foreach ($quoteItems as $item) {
            if ($item->getParentItem()) {
                continue;
            }

            $children = $item->getChildren();
            if ($children && $item->isShipSeparately()) {
                foreach ($children as $child) {
                    if ($child->getProduct()->getIsVirtual()) {
                        $quote->setVirtualItemsQty($quote->getVirtualItemsQty() + $child->getQty() * $item->getQty());
                    }
                }
            }

            if ($item->getProduct()->getIsVirtual()) {
                $quote->setVirtualItemsQty($quote->getVirtualItemsQty() + $item->getQty());
            }
            $quote->setItemsCount($quote->getItemsCount() + 1);
            $quote->setItemsQty((float)$quote->getItemsQty() + $item->getQty());
        }

        return $quote;
    }
}
