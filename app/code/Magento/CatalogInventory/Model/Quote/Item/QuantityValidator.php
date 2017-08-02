<?php
/**
 * Product inventory data validator
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Model\Quote\Item;

use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\CatalogInventory\Api\StockStateInterface;
use Magento\CatalogInventory\Helper\Data;
use Magento\CatalogInventory\Model\Quote\Item\QuantityValidator\Initializer\Option;
use Magento\CatalogInventory\Model\Quote\Item\QuantityValidator\Initializer\StockItem;
use Magento\CatalogInventory\Model\Stock;
use Magento\Framework\Event\Observer;
use Magento\Framework\Exception\LocalizedException;

/**
 * @api
 * @since 2.0.0
 */
class QuantityValidator
{
    /**
     * @var QuantityValidator\Initializer\Option
     * @since 2.0.0
     */
    protected $optionInitializer;

    /**
     * @var QuantityValidator\Initializer\StockItem
     * @since 2.0.0
     */
    protected $stockItemInitializer;

    /**
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface
     * @since 2.0.0
     */
    protected $stockRegistry;

    /**
     * @var \Magento\CatalogInventory\Api\StockStateInterface
     * @since 2.0.0
     */
    protected $stockState;

    /**
     * @param Option $optionInitializer
     * @param StockItem $stockItemInitializer
     * @param StockRegistryInterface $stockRegistry
     * @param StockStateInterface $stockState
     * @return void
     * @since 2.0.0
     */
    public function __construct(
        Option $optionInitializer,
        StockItem $stockItemInitializer,
        StockRegistryInterface $stockRegistry,
        StockStateInterface $stockState
    ) {
        $this->optionInitializer = $optionInitializer;
        $this->stockItemInitializer = $stockItemInitializer;
        $this->stockRegistry = $stockRegistry;
        $this->stockState = $stockState;
    }

    /**
     * Add error information to Quote Item
     *
     * @param \Magento\Framework\DataObject $result
     * @param \Magento\Quote\Model\Quote\Item $quoteItem
     * @param bool $removeError
     * @return void
     * @since 2.2.0
     */
    private function addErrorInfoToQuote($result, $quoteItem)
    {
        $quoteItem->addErrorInfo(
            'cataloginventory',
            Data::ERROR_QTY,
            $result->getMessage()
        );

        $quoteItem->getQuote()->addErrorInfo(
            $result->getQuoteMessageIndex(),
            'cataloginventory',
            Data::ERROR_QTY,
            $result->getQuoteMessage()
        );
    }

    /**
     * Check product inventory data when quote item quantity declaring
     *
     * @param \Magento\Framework\Event\Observer $observer
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @since 2.0.0
     */
    public function validate(Observer $observer)
    {
        /* @var $quoteItem \Magento\Quote\Model\Quote\Item */
        $quoteItem = $observer->getEvent()->getItem();
        if (!$quoteItem ||
            !$quoteItem->getProductId() ||
            !$quoteItem->getQuote() ||
            $quoteItem->getQuote()->getIsSuperMode()
        ) {
            return;
        }
        $product = $quoteItem->getProduct();
        $qty = $quoteItem->getQty();

        /* @var \Magento\CatalogInventory\Model\Stock\Item $stockItem */
        $stockItem = $this->stockRegistry->getStockItem($product->getId(), $product->getStore()->getWebsiteId());
        if (!$stockItem instanceof StockItemInterface) {
            throw new LocalizedException(__('The stock item for Product is not valid.'));
        }

        /* @var \Magento\CatalogInventory\Api\Data\StockStatusInterface $stockStatus */
        $stockStatus = $this->stockRegistry->getStockStatus($product->getId(), $product->getStore()->getWebsiteId());

        /* @var \Magento\CatalogInventory\Api\Data\StockStatusInterface $parentStockStatus */
        $parentStockStatus = false;

        /**
         * Check if product in stock. For composite products check base (parent) item stock status
         */
        if ($quoteItem->getParentItem()) {
            $product = $quoteItem->getParentItem()->getProduct();
            $parentStockStatus = $this->stockRegistry->getStockStatus(
                $product->getId(),
                $product->getStore()->getWebsiteId()
            );
        }

        if ($stockStatus) {
            if ($stockStatus->getStockStatus() === Stock::STOCK_OUT_OF_STOCK
                    || $parentStockStatus && $parentStockStatus->getStockStatus() == Stock::STOCK_OUT_OF_STOCK
            ) {
                $quoteItem->addErrorInfo(
                    'cataloginventory',
                    Data::ERROR_QTY,
                    __('This product is out of stock.')
                );
                $quoteItem->getQuote()->addErrorInfo(
                    'stock',
                    'cataloginventory',
                    Data::ERROR_QTY,
                    __('Some of the products are out of stock.')
                );
                return;
            } else {
                // Delete error from item and its quote, if it was set due to item out of stock
                $this->_removeErrorsFromQuoteAndItem($quoteItem, Data::ERROR_QTY);
            }
        }

        /**
         * Check item for options
         */
        if (($options = $quoteItem->getQtyOptions()) && $qty > 0) {
            $qty = $product->getTypeInstance()->prepareQuoteItemQty($qty, $product);
            $quoteItem->setData('qty', $qty);
            if ($stockStatus) {
                $result = $this->stockState->checkQtyIncrements(
                    $product->getId(),
                    $qty,
                    $product->getStore()->getWebsiteId()
                );
                if ($result->getHasError()) {
                    $quoteItem->addErrorInfo(
                        'cataloginventory',
                        Data::ERROR_QTY_INCREMENTS,
                        $result->getMessage()
                    );

                    $quoteItem->getQuote()->addErrorInfo(
                        $result->getQuoteMessageIndex(),
                        'cataloginventory',
                        Data::ERROR_QTY_INCREMENTS,
                        $result->getQuoteMessage()
                    );
                } else {
                    // Delete error from item and its quote, if it was set due to qty problems
                    $this->_removeErrorsFromQuoteAndItem(
                        $quoteItem,
                        Data::ERROR_QTY_INCREMENTS
                    );
                }
            }
            // variable to keep track if we have previously encountered an error in one of the options
            $removeError = true;

            foreach ($options as $option) {
                $result = $this->optionInitializer->initialize($option, $quoteItem, $qty);
                if ($result->getHasError()) {
                    $option->setHasError(true);
                    //Setting this to false, so no error statuses are cleared
                    $removeError = false;
                    $this->addErrorInfoToQuote($result, $quoteItem, $removeError);
                }
            }
            if ($removeError) {
                $this->_removeErrorsFromQuoteAndItem($quoteItem, Data::ERROR_QTY);
            }
        } else {
            if ($quoteItem->getParentItem() === null) {
                $result = $this->stockItemInitializer->initialize($stockItem, $quoteItem, $qty);
                if ($result->getHasError()) {
                    $this->addErrorInfoToQuote($result, $quoteItem);
                } else {
                    $this->_removeErrorsFromQuoteAndItem($quoteItem, Data::ERROR_QTY);
                }
            }
        }
    }

    /**
     * Removes error statuses from quote and item, set by this observer
     *
     * @param \Magento\Quote\Model\Quote\Item $item
     * @param int $code
     * @return void
     * @since 2.0.0
     */
    protected function _removeErrorsFromQuoteAndItem($item, $code)
    {
        if ($item->getHasError()) {
            $params = ['origin' => 'cataloginventory', 'code' => $code];
            $item->removeErrorInfosByParams($params);
        }

        $quote = $item->getQuote();
        if ($quote->getHasError()) {
            $quoteItems = $quote->getItemsCollection();
            $canRemoveErrorFromQuote = true;
            foreach ($quoteItems as $quoteItem) {
                if ($quoteItem->getItemId() == $item->getItemId()) {
                    continue;
                }

                $errorInfos = $quoteItem->getErrorInfos();
                foreach ($errorInfos as $errorInfo) {
                    if ($errorInfo['code'] == $code) {
                        $canRemoveErrorFromQuote = false;
                        break;
                    }
                }

                if (!$canRemoveErrorFromQuote) {
                    break;
                }
            }

            if ($canRemoveErrorFromQuote) {
                $params = ['origin' => 'cataloginventory', 'code' => $code];
                $quote->removeErrorInfosByParams(null, $params);
            }
        }
    }
}
