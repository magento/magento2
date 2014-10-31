<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\CatalogInventory\Model\Quote\Item\QuantityValidator\Initializer;

use Magento\Sales\Model\Quote\Item;
use Magento\CatalogInventory\Model\Quote\Item\QuantityValidator\QuoteItemQtyList;

/**
 * Class QtyProcessor
 */
class QtyProcessor
{
    /**
     * @var QuoteItemQtyList
     */
    protected $quoteItemQtyList;

    /**
     * @var Item
     */
    protected $item;

    /**
     * @param QuoteItemQtyList $quoteItemQtyList
     */
    public function __construct(QuoteItemQtyList $quoteItemQtyList)
    {
        $this->quoteItemQtyList = $quoteItemQtyList;
    }

    /**
     * @param Item $quoteItem
     * @return $this
     */
    public function setItem(Item $quoteItem)
    {
        $this->item = $quoteItem;
        return $this;
    }

    /**
     * @param float $qty
     * @return float|int
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
