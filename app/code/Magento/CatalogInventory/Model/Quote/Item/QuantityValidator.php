<?php
/**
 * Product inventory data validator
 *
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
namespace Magento\CatalogInventory\Model\Quote\Item;

class QuantityValidator
{
    /**
     * @var QuantityValidator\Initializer\Option
     */
    protected $optionInitializer;

    /**
     * @var QuantityValidator\Initializer\StockItem
     */
    protected $stockItemInitializer;

    /**
     * @var \Magento\CatalogInventory\Model\Stock\ItemFactory
     */
    protected $stockItemFactory;

    /**
     * @param QuantityValidator\Initializer\Option $optionInitializer
     * @param QuantityValidator\Initializer\StockItem $stockItemInitializer
     * @param \Magento\CatalogInventory\Model\Stock\ItemFactory $stockItemFactory
     */
    public function __construct(
        QuantityValidator\Initializer\Option $optionInitializer,
        QuantityValidator\Initializer\StockItem $stockItemInitializer,
        \Magento\CatalogInventory\Model\Stock\ItemFactory $stockItemFactory
    ) {
        $this->optionInitializer = $optionInitializer;
        $this->stockItemInitializer = $stockItemInitializer;
        $this->stockItemFactory = $stockItemFactory;
    }

    /**
     * Check product inventory data when quote item quantity declaring
     *
     * @param \Magento\Framework\Event\Observer $observer
     *
     * @return void
     * @throws \Magento\Framework\Model\Exception
     */
    public function validate(\Magento\Framework\Event\Observer $observer)
    {
        /* @var $quoteItem \Magento\Sales\Model\Quote\Item */
        $quoteItem = $observer->getEvent()->getItem();

        if (!$quoteItem ||
            !$quoteItem->getProductId() ||
            !$quoteItem->getQuote() ||
            $quoteItem->getQuote()->getIsSuperMode()
        ) {
            return;
        }

        $qty = $quoteItem->getQty();

        /** @var \Magento\CatalogInventory\Model\Stock\Item $stockItem */
        $stockItem = $this->stockItemFactory->create()->loadByProduct($quoteItem->getProduct());

        $parentStockItem = false;

        /**
         * Check if product in stock. For composite products check base (parent) item stock status
         */
        if ($quoteItem->getParentItem()) {
            $parentStockItem = $this->stockItemFactory->create()
                ->loadByProduct($quoteItem->getParentItem()->getProduct());
        }

        if ($stockItem) {
            if (!$stockItem->getIsInStock() || $parentStockItem && !$parentStockItem->getIsInStock()) {
                $quoteItem->addErrorInfo(
                    'cataloginventory',
                    \Magento\CatalogInventory\Helper\Data::ERROR_QTY,
                    __('This product is out of stock.')
                );
                $quoteItem->getQuote()->addErrorInfo(
                    'stock',
                    'cataloginventory',
                    \Magento\CatalogInventory\Helper\Data::ERROR_QTY,
                    __('Some of the products are currently out of stock.')
                );
                return;
            } else {
                // Delete error from item and its quote, if it was set due to item out of stock
                $this->_removeErrorsFromQuoteAndItem($quoteItem, \Magento\CatalogInventory\Helper\Data::ERROR_QTY);
            }
        }

        /**
         * Check item for options
         */
        if (($options = $quoteItem->getQtyOptions()) && $qty > 0) {
            $qty = $quoteItem->getProduct()->getTypeInstance()->prepareQuoteItemQty($qty, $quoteItem->getProduct());
            $quoteItem->setData('qty', $qty);

            if ($stockItem) {
                $result = $stockItem->checkQtyIncrements($qty);
                if ($result->getHasError()) {
                    $quoteItem->addErrorInfo(
                        'cataloginventory',
                        \Magento\CatalogInventory\Helper\Data::ERROR_QTY_INCREMENTS,
                        $result->getMessage()
                    );

                    $quoteItem->getQuote()->addErrorInfo(
                        $result->getQuoteMessageIndex(),
                        'cataloginventory',
                        \Magento\CatalogInventory\Helper\Data::ERROR_QTY_INCREMENTS,
                        $result->getQuoteMessage()
                    );
                } else {
                    // Delete error from item and its quote, if it was set due to qty problems
                    $this->_removeErrorsFromQuoteAndItem(
                        $quoteItem,
                        \Magento\CatalogInventory\Helper\Data::ERROR_QTY_INCREMENTS
                    );
                }
            }

            foreach ($options as $option) {

                $result = $this->optionInitializer->initialize($option, $quoteItem, $qty);
                if ($result->getHasError()) {
                    $option->setHasError(true);

                    $quoteItem->addErrorInfo(
                        'cataloginventory',
                        \Magento\CatalogInventory\Helper\Data::ERROR_QTY,
                        $result->getMessage()
                    );

                    $quoteItem->getQuote()->addErrorInfo(
                        $result->getQuoteMessageIndex(),
                        'cataloginventory',
                        \Magento\CatalogInventory\Helper\Data::ERROR_QTY,
                        $result->getQuoteMessage()
                    );
                } else {
                    // Delete error from item and its quote, if it was set due to qty lack
                    $this->_removeErrorsFromQuoteAndItem($quoteItem, \Magento\CatalogInventory\Helper\Data::ERROR_QTY);
                }
            }
        } else {
            /* @var $stockItem \Magento\CatalogInventory\Model\Stock\Item */
            if (!$stockItem instanceof \Magento\CatalogInventory\Model\Stock\Item) {
                throw new \Magento\Framework\Model\Exception(__('The stock item for Product in option is not valid.'));
            }

            $result = $this->stockItemInitializer->initialize($stockItem, $quoteItem, $qty);

            if ($result->getHasError()) {
                $quoteItem->addErrorInfo(
                    'cataloginventory',
                    \Magento\CatalogInventory\Helper\Data::ERROR_QTY,
                    $result->getMessage()
                );

                $quoteItem->getQuote()->addErrorInfo(
                    $result->getQuoteMessageIndex(),
                    'cataloginventory',
                    \Magento\CatalogInventory\Helper\Data::ERROR_QTY,
                    $result->getQuoteMessage()
                );
            } else {
                // Delete error from item and its quote, if it was set due to qty lack
                $this->_removeErrorsFromQuoteAndItem($quoteItem, \Magento\CatalogInventory\Helper\Data::ERROR_QTY);
            }
        }
    }

    /**
     * Removes error statuses from quote and item, set by this observer
     *
     * @param \Magento\Sales\Model\Quote\Item $item
     * @param int $code
     * @return void
     */
    protected function _removeErrorsFromQuoteAndItem($item, $code)
    {
        if ($item->getHasError()) {
            $params = array('origin' => 'cataloginventory', 'code' => $code);
            $item->removeErrorInfosByParams($params);
        }

        $quote = $item->getQuote();
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

        if ($quote->getHasError() && $canRemoveErrorFromQuote) {
            $params = array('origin' => 'cataloginventory', 'code' => $code);
            $quote->removeErrorInfosByParams(null, $params);
        }
    }
}
