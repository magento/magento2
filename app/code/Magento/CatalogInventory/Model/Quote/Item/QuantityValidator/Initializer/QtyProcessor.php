<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Model\Quote\Item\QuantityValidator\Initializer;

use Magento\CatalogInventory\Model\Quote\Item\QuantityValidator\QuoteItemQtyList;
use Magento\Quote\Model\Quote\Item;

/**
 * @deprecated 2.2.0 No more used
 * @since 2.0.0
 */
class QtyProcessor
{
    /**
     * @var QuoteItemQtyList
     * @since 2.0.0
     */
    protected $quoteItemQtyList;

    /**
     * @var Item
     * @since 2.0.0
     */
    protected $item;

    /**
     * @param QuoteItemQtyList $quoteItemQtyList
     * @since 2.0.0
     */
    public function __construct(QuoteItemQtyList $quoteItemQtyList)
    {
        $this->quoteItemQtyList = $quoteItemQtyList;
    }

    /**
     * @param Item $quoteItem
     * @return $this
     * @deprecated 2.2.0 No more used
     * @since 2.0.0
     */
    public function setItem(Item $quoteItem)
    {
        $this->item = $quoteItem;
        return $this;
    }

    /**
     * @param float $qty
     * @return float|int
     * @deprecated 2.2.0 No more used
     * @since 2.0.0
     */
    public function getRowQty($qty)
    {
        $rowQty = $qty;
        if ($this->item->getParentItem()) {
            $rowQty = $this->item->getParentItem()->getQty() * $qty;
        }
        return $rowQty;
    }

    /**
     * @param int $qty
     * @return int
     * @deprecated 2.2.0 No more used
     * @since 2.0.0
     */
    public function getQtyForCheck($qty)
    {
        if (!$this->item->getParentItem()) {
            $increaseQty = $this->item->getQtyToAdd() ? $this->item->getQtyToAdd() : $qty;
            return $this->quoteItemQtyList->getQty(
                $this->item->getProduct()->getId(),
                $this->item->getId(),
                $this->item->getQuoteId(),
                $increaseQty
            );
        }
        return $this->quoteItemQtyList->getQty(
            $this->item->getProduct()->getId(),
            $this->item->getId(),
            $this->item->getQuoteId(),
            0
        );
    }
}
