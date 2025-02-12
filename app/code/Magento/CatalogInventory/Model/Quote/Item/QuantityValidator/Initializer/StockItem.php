<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Model\Quote\Item\QuantityValidator\Initializer;

use Magento\Catalog\Model\ProductTypes\ConfigInterface;
use Magento\CatalogInventory\Api\StockStateInterface;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\CatalogInventory\Model\Quote\Item\QuantityValidator\QuoteItemQtyList;
use Magento\CatalogInventory\Model\Spi\StockStateProviderInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Quote\Model\Quote\Item;

/**
 * Class StockItem initializes stock item and populates it with data
 */
class StockItem
{
    /**
     * @var QuoteItemQtyList
     */
    protected $quoteItemQtyList;

    /**
     * @var ConfigInterface
     */
    protected $typeConfig;

    /**
     * @var StockStateInterface
     */
    protected $stockState;

    /**
     * @var StockStateProviderInterface
     * @deprecated
     * @see was overriding ItemBackorders value with the Default Scope value; caused discrepancy in multistock config
     */
    private $stockStateProvider;

    /**
     * @param ConfigInterface $typeConfig
     * @param QuoteItemQtyList $quoteItemQtyList
     * @param StockStateInterface $stockState
     * @param StockStateProviderInterface|null $stockStateProvider
     */
    public function __construct(
        ConfigInterface $typeConfig,
        QuoteItemQtyList $quoteItemQtyList,
        StockStateInterface $stockState,
        ?StockStateProviderInterface $stockStateProvider = null
    ) {
        $this->quoteItemQtyList = $quoteItemQtyList;
        $this->typeConfig = $typeConfig;
        $this->stockState = $stockState;
        $this->stockStateProvider = $stockStateProvider ?: ObjectManager::getInstance()
            ->get(StockStateProviderInterface::class);
    }

    /**
     * Initialize stock item
     *
     * @param StockItemInterface $stockItem
     * @param Item $quoteItem
     * @param int $qty
     *
     * @return \Magento\Framework\DataObject
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function initialize(
        StockItemInterface $stockItem,
        Item $quoteItem,
        $qty
    ) {
        $product = $quoteItem->getProduct();
        $quoteItemId = $quoteItem->getId();
        $quoteId = $quoteItem->getQuoteId();
        $productId = $product->getId();
        /**
         * When we work with subitem
         */
        if ($quoteItem->getParentItem()) {
            $rowQty = $quoteItem->getParentItem()->getQty() * $qty;
            /**
             * we are using 0 because original qty was processed
             */
            $qtyForCheck = $this->quoteItemQtyList
                ->getQty($productId, $quoteItemId, $quoteId, 0);
        } else {
            $increaseQty = $quoteItem->getQtyToAdd() ? $quoteItem->getQtyToAdd() : $qty;
            $rowQty = $qty;
            $qtyForCheck = $this->quoteItemQtyList->getQty(
                $productId,
                $quoteItemId,
                $quoteId,
                $increaseQty
            );
        }

        $productTypeCustomOption = $product->getCustomOption('product_type');
        if ($productTypeCustomOption !== null) {
            // Check if product related to current item is a part of product that represents product set
            if ($this->typeConfig->isProductSet($productTypeCustomOption->getValue())) {
                $stockItem->setIsChildItem(true);
            }
        }

        $stockItem->setProductName($product->getName());

        /** @var \Magento\Framework\DataObject $result */
        $result = $this->stockState->checkQuoteItemQty(
            $productId,
            $rowQty,
            $qtyForCheck,
            $qty,
            $product->getStore()->getWebsiteId()
        );

        if ($result->getHasError() === true && in_array($result->getErrorCode(), ['qty_available', 'out_stock'])) {
            $quoteItem->setHasError(true);
        }

        if ($stockItem->hasIsChildItem()) {
            $stockItem->unsIsChildItem();
        }

        if ($result->getItemIsQtyDecimal() !== null) {
            $quoteItem->setIsQtyDecimal($result->getItemIsQtyDecimal());
            if ($quoteItem->getParentItem()) {
                $quoteItem->getParentItem()->setIsQtyDecimal($result->getItemIsQtyDecimal());
            }
        }

        /**
         * Just base (parent) item qty can be changed
         * qty of child products are declared just during add process
         * exception for updating also managed by product type
         */
        if ($result->getHasQtyOptionUpdate() && (!$quoteItem->getParentItem() ||
                $quoteItem->getParentItem()->getProduct()->getTypeInstance()->getForceChildItemQtyChanges(
                    $quoteItem->getParentItem()->getProduct()
                )
            )
        ) {
            $quoteItem->setData('qty', $result->getOrigQty());
        }

        if ($result->getItemUseOldQty() !== null) {
            $quoteItem->setUseOldQty($result->getItemUseOldQty());
        }

        if ($result->getMessage() !== null) {
            $quoteItem->setMessage($result->getMessage());
        }

        if ($result->getItemBackorders() !== null) {
            $quoteItem->setBackorders($result->getItemBackorders());
        }

        $quoteItem->setStockStateResult($result);

        return $result;
    }
}
