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

use Magento\CatalogInventory\Model\Quote\Item\QuantityValidator\QuoteItemQtyList;

class Option
{
    /**
     * @var QuoteItemQtyList
     */
    protected $quoteItemQtyList;

    /**
     * @var \Magento\CatalogInventory\Model\Stock\ItemRegistry
     */
    protected $stockItemRegistry;

    /**
     * @param QuoteItemQtyList $quoteItemQtyList
     * @param \Magento\CatalogInventory\Model\Stock\ItemRegistry $stockItemRegistry
     */
    public function __construct(
        QuoteItemQtyList $quoteItemQtyList,
        \Magento\CatalogInventory\Model\Stock\ItemRegistry $stockItemRegistry
    ) {
        $this->quoteItemQtyList = $quoteItemQtyList;
        $this->stockItemRegistry = $stockItemRegistry;
    }

    /**
     * Init stock item
     *
     * @param \Magento\Sales\Model\Quote\Item\Option $option
     * @param \Magento\Sales\Model\Quote\Item $quoteItem
     *
     * @return \Magento\CatalogInventory\Model\Stock\Item
     * @throws \Magento\Framework\Model\Exception
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getStockItem(
        \Magento\Sales\Model\Quote\Item\Option $option,
        \Magento\Sales\Model\Quote\Item $quoteItem
    ) {
        $stockItem = $this->stockItemRegistry->retrieve($option->getProduct()->getId());
        if (!$stockItem->getId()) {
            throw new \Magento\Framework\Model\Exception(__('The stock item for Product in option is not valid.'));
        }
        /**
         * define that stock item is child for composite product
         */
        $stockItem->setIsChildItem(true);
        /**
         * don't check qty increments value for option product
         */
        $stockItem->setSuppressCheckQtyIncrements(true);

        return $stockItem;
    }

    /**
     * Initialize item option
     *
     * @param \Magento\Sales\Model\Quote\Item\Option $option
     * @param \Magento\Sales\Model\Quote\Item $quoteItem
     * @param int $qty
     *
     * @return \Magento\Framework\Object
     * @throws \Magento\Framework\Model\Exception
     */
    public function initialize(
        \Magento\Sales\Model\Quote\Item\Option $option,
        \Magento\Sales\Model\Quote\Item $quoteItem,
        $qty
    ) {
        $optionValue = $option->getValue();
        $optionQty = $qty * $optionValue;
        $increaseOptionQty = ($quoteItem->getQtyToAdd() ? $quoteItem->getQtyToAdd() : $qty) * $optionValue;
        $qtyForCheck = $this->quoteItemQtyList->getQty(
            $option->getProduct()->getId(),
            $quoteItem->getId(),
            $quoteItem->getQuoteId(),
            $increaseOptionQty
        );

        $stockItem = $this->getStockItem($option, $quoteItem);
        $result = $stockItem->checkQuoteItemQty($optionQty, $qtyForCheck, $optionValue);

        if (!is_null($result->getItemIsQtyDecimal())) {
            $option->setIsQtyDecimal($result->getItemIsQtyDecimal());
        }

        if ($result->getHasQtyOptionUpdate()) {
            $option->setHasQtyOptionUpdate(true);
            $quoteItem->updateQtyOption($option, $result->getOrigQty());
            $option->setValue($result->getOrigQty());
            /**
             * if option's qty was updates we also need to update quote item qty
             */
            $quoteItem->setData('qty', intval($qty));
        }
        if (!is_null($result->getMessage())) {
            $option->setMessage($result->getMessage());
            $quoteItem->setMessage($result->getMessage());
        }
        if (!is_null($result->getItemBackorders())) {
            $option->setBackorders($result->getItemBackorders());
        }

        $stockItem->unsIsChildItem();

        return $result;
    }
}
