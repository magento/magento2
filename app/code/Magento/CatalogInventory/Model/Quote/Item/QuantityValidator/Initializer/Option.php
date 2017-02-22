<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Model\Quote\Item\QuantityValidator\Initializer;

use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\CatalogInventory\Api\StockStateInterface;
use Magento\CatalogInventory\Model\Quote\Item\QuantityValidator\QuoteItemQtyList;

class Option
{
    /**
     * @var QuoteItemQtyList
     */
    protected $quoteItemQtyList;

    /**
     * @var StockRegistryInterface
     */
    protected $stockRegistry;

    /**
     * @var StockStateInterface
     */
    protected $stockState;

    /**
     * @param QuoteItemQtyList $quoteItemQtyList
     * @param StockRegistryInterface $stockRegistry
     * @param StockStateInterface $stockState
     */
    public function __construct(
        QuoteItemQtyList $quoteItemQtyList,
        StockRegistryInterface $stockRegistry,
        StockStateInterface $stockState
    ) {
        $this->quoteItemQtyList = $quoteItemQtyList;
        $this->stockRegistry = $stockRegistry;
        $this->stockState = $stockState;
    }

    /**
     * Init stock item
     *
     * @param \Magento\Quote\Model\Quote\Item\Option $option
     * @param \Magento\Quote\Model\Quote\Item $quoteItem
     *
     * @return \Magento\CatalogInventory\Model\Stock\Item
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getStockItem(
        \Magento\Quote\Model\Quote\Item\Option $option,
        \Magento\Quote\Model\Quote\Item $quoteItem
    ) {
        $stockItem = $this->stockRegistry->getStockItem(
            $option->getProduct()->getId(),
            $quoteItem->getStore()->getWebsiteId()
        );
        if (!$stockItem->getItemId()) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('The stock item for Product in option is not valid.')
            );
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
     * @param \Magento\Quote\Model\Quote\Item\Option $option
     * @param \Magento\Quote\Model\Quote\Item $quoteItem
     * @param int $qty
     *
     * @return \Magento\Framework\DataObject
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function initialize(
        \Magento\Quote\Model\Quote\Item\Option $option,
        \Magento\Quote\Model\Quote\Item $quoteItem,
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
        $result = $this->stockState->checkQuoteItemQty(
            $option->getProduct()->getId(),
            $optionQty,
            $qtyForCheck,
            $optionValue,
            $option->getProduct()->getStore()->getWebsiteId()
        );

        if ($result->getItemIsQtyDecimal() !== null) {
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
        if ($result->getMessage() !== null) {
            $option->setMessage($result->getMessage());
            $quoteItem->setMessage($result->getMessage());
        }
        if ($result->getItemBackorders() !== null) {
            $option->setBackorders($result->getItemBackorders());
        }

        $stockItem->unsIsChildItem();

        return $result;
    }
}
