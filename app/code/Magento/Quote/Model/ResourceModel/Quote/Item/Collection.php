<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Model\ResourceModel\Quote\Item;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Catalog\Model\Product\Attribute\Source\Status as ProductStatus;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Item as QuoteItem;
use Magento\Quote\Model\ResourceModel\Quote\Item as ResourceQuoteItem;

/**
 * Quote item resource collection
 *
 * @api
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 100.0.2
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\VersionControl\Collection
{
    /**
     * Collection quote instance
     *
     * @var \Magento\Quote\Model\Quote
     */
    protected $_quote;

    /**
     * Product Ids array
     *
     * @var int[]
     */
    protected $_productIds = [];

    /**
     * @var \Magento\Quote\Model\ResourceModel\Quote\Item\Option\CollectionFactory
     */
    protected $_itemOptionCollectionFactory;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $_productCollectionFactory;

    /**
     * @var \Magento\Quote\Model\Quote\Config
     */
    protected $_quoteConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|null
     */
    private $storeManager;

    /**
     * @var bool $recollectQuote
     */
    private $recollectQuote = false;

    /**
     * @var \Magento\Quote\Model\Config
     */
    private $config;

    /**
     * @param \Magento\Framework\Data\Collection\EntityFactory $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot $entitySnapshot
     * @param Option\CollectionFactory $itemOptionCollectionFactory
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param \Magento\Quote\Model\Quote\Config $quoteConfig
     * @param \Magento\Framework\DB\Adapter\AdapterInterface $connection
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource
     * @param \Magento\Store\Model\StoreManagerInterface|null $storeManager
     * @param \Magento\Quote\Model\Config|null $config
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactory $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot $entitySnapshot,
        \Magento\Quote\Model\ResourceModel\Quote\Item\Option\CollectionFactory $itemOptionCollectionFactory,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Quote\Model\Quote\Config $quoteConfig,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null,
        \Magento\Store\Model\StoreManagerInterface $storeManager = null,
        \Magento\Quote\Model\Config $config = null
    ) {
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $entitySnapshot,
            $connection,
            $resource
        );
        $this->_itemOptionCollectionFactory = $itemOptionCollectionFactory;
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_quoteConfig = $quoteConfig;
        $this->config = $config ?:
            \Magento\Framework\App\ObjectManager::getInstance()->get(\Magento\Quote\Model\Config::class);

        // Backward compatibility constructor parameters
        $this->storeManager = $storeManager ?:
            \Magento\Framework\App\ObjectManager::getInstance()->get(\Magento\Store\Model\StoreManagerInterface::class);
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(QuoteItem::class, ResourceQuoteItem::class);
    }

    /**
     * Retrieve store Id (From Quote)
     *
     * @return int
     */
    public function getStoreId(): int
    {
        // Fallback to current storeId if no quote is provided
        // (see https://github.com/magento/magento2/commit/9d3be732a88884a66d667b443b3dc1655ddd0721)
        return $this->_quote === null ?
            (int) $this->storeManager->getStore()->getId() : (int) $this->_quote->getStoreId();
    }

    /**
     * Set Quote object to Collection.
     *
     * @param Quote $quote
     * @return $this
     */
    public function setQuote($quote): self
    {
        $this->_quote = $quote;
        $quoteId = $quote->getId();
        if ($quoteId) {
            $this->addFieldToFilter('quote_id', $quote->getId());
        } else {
            $this->_totalRecords = 0;
            $this->_setIsLoaded(true);
        }
        return $this;
    }

    /**
     * Reset the collection and inner join it to quotes table.
     *
     * Optionally can select items with specified product id only
     *
     * @param string $quotesTableName
     * @param int $productId
     * @return $this
     */
    public function resetJoinQuotes($quotesTableName, $productId = null): self
    {
        $this->getSelect()->reset()->from(
            ['qi' => $this->getResource()->getMainTable()],
            ['item_id', 'qty', 'quote_id']
        )->joinInner(
            ['q' => $quotesTableName],
            'qi.quote_id = q.entity_id',
            ['store_id', 'items_qty', 'items_count']
        );
        if ($productId) {
            $this->getSelect()->where('qi.product_id = ?', (int)$productId);
        }
        return $this;
    }

    /**
     * After load processing.
     *
     * @return $this
     */
    protected function _afterLoad(): self
    {
        parent::_afterLoad();

        $productIds = [];
        foreach ($this as $item) {
            // Assign parent items
            if ($item->getParentItemId()) {
                $item->setParentItem($this->getItemById($item->getParentItemId()));
            }
            if ($this->_quote) {
                $item->setQuote($this->_quote);
            }
            // Collect quote products ids
            $productIds[] = (int)$item->getProductId();
        }
        $this->_productIds = array_merge($this->_productIds, $productIds);
        $this->removeItemsWithAbsentProducts();
        /**
         * Assign options and products
         */
        $this->_assignOptions();
        $this->_assignProducts();
        $this->resetItemsDataChanged();

        return $this;
    }

    /**
     * Add options to items.
     *
     * @return $this
     */
    protected function _assignOptions(): self
    {
        $itemIds = array_keys($this->_items);
        $optionCollection = $this->_itemOptionCollectionFactory->create()->addItemFilter($itemIds);
        foreach ($this as $item) {
            $item->setOptions($optionCollection->getOptionsByItem($item));
        }
        $productIds = $optionCollection->getProductIds();
        $this->_productIds = array_merge($this->_productIds, $productIds);

        return $this;
    }

    /**
     * Add products to items and item options.
     *
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function _assignProducts(): self
    {
        \Magento\Framework\Profiler::start('QUOTE:' . __METHOD__, ['group' => 'QUOTE', 'method' => __METHOD__]);
        $productCollection = $this->_productCollectionFactory->create()->setStoreId(
            $this->getStoreId()
        )->addIdFilter(
            $this->_productIds
        )->addAttributeToSelect(
            $this->_quoteConfig->getProductAttributes()
        );
        $this->skipStockStatusFilter($productCollection);
        $productCollection->addOptionsToResult()->addStoreFilter()->addUrlRewrite();

        $this->_eventManager->dispatch(
            'prepare_catalog_product_collection_prices',
            ['collection' => $productCollection, 'store_id' => $this->getStoreId()]
        );
        $this->_eventManager->dispatch(
            'sales_quote_item_collection_products_after_load',
            ['collection' => $productCollection]
        );

        foreach ($this as $item) {
            /** @var ProductInterface $product */
            $product = $productCollection->getItemById($item->getProductId());
            try {
                /** @var QuoteItem $item */
                $parentItem = $item->getParentItem();
                $parentProduct = $parentItem ? $parentItem->getProduct() : null;
            } catch (NoSuchEntityException $exception) {
                $parentItem = null;
                $parentProduct = null;
                $this->_logger->error($exception);
            }
            $qtyOptions = [];
            if ($this->isValidProduct($product) && (!$parentItem || $this->isValidProduct($parentProduct))) {
                $product->setCustomOptions([]);
                $optionProductIds = $this->getOptionProductIds($item, $product, $productCollection);
                foreach ($optionProductIds as $optionProductId) {
                    $qtyOption = $item->getOptionByCode('product_qty_' . $optionProductId);
                    if ($qtyOption) {
                        $qtyOptions[$optionProductId] = $qtyOption;
                    }
                }
            } else {
                $item->isDeleted(true);
                $this->recollectQuote = true;
            }
            if (!$item->isDeleted()) {
                $item->setQtyOptions($qtyOptions)->setProduct($product);
                if ($this->config->isEnabled()) {
                    $item->checkData();
                }

            }
        }
        if ($this->recollectQuote && $this->_quote) {
            $this->_quote->setTotalsCollectedFlag(false);
        }
        \Magento\Framework\Profiler::stop('QUOTE:' . __METHOD__);

        return $this;
    }

    /**
     * Get product Ids from option.
     *
     * @param QuoteItem $item
     * @param ProductInterface $product
     * @param ProductCollection $productCollection
     * @return array
     */
    private function getOptionProductIds(
        QuoteItem $item,
        ProductInterface $product,
        ProductCollection $productCollection
    ): array {
        $optionProductIds = [];
        foreach ($item->getOptions() as $option) {
            /**
             * Call type-specific logic for product associated with quote item
             */
            $product->getTypeInstance()->assignProductToOption(
                $productCollection->getItemById($option->getProductId()),
                $option,
                $product
            );

            if (is_object($option->getProduct()) && $option->getProduct()->getId() != $product->getId()) {
                $isValidProduct = $this->isValidProduct($option->getProduct());
                if (!$isValidProduct && !$item->isDeleted()) {
                    $item->isDeleted(true);
                    $this->recollectQuote = true;
                    continue;
                }
                $optionProductIds[$option->getProduct()->getId()] = $option->getProduct()->getId();
            }
        }

        return $optionProductIds;
    }

    /**
     * Check is valid product.
     *
     * @param ProductInterface $product
     * @return bool
     */
    private function isValidProduct(?ProductInterface $product): bool
    {
        $result = ($product && (int)$product->getStatus() !== ProductStatus::STATUS_DISABLED);

        return $result;
    }

    /**
     * Prevents adding stock status filter to the collection of products.
     *
     * @param ProductCollection $productCollection
     * @return void
     *
     * @see \Magento\CatalogInventory\Helper\Stock::addIsInStockFilterToCollection
     */
    private function skipStockStatusFilter(ProductCollection $productCollection): void
    {
        $productCollection->setFlag('has_stock_status_filter', true);
    }

    /**
     * Find and remove quote items with non existing products
     *
     * @return void
     */
    private function removeItemsWithAbsentProducts(): void
    {
        if (count($this->_productIds) === 0) {
            return;
        }

        $productCollection = $this->_productCollectionFactory->create()->addIdFilter($this->_productIds);
        $existingProductsIds = $productCollection->getAllIds();
        $absentProductsIds = array_diff($this->_productIds, $existingProductsIds);
        // Remove not existing products from items collection
        if (!empty($absentProductsIds)) {
            foreach ($absentProductsIds as $productIdToExclude) {
                /** @var \Magento\Quote\Model\Quote\Item $quoteItem */
                $quoteItem = $this->getItemByColumnValue('product_id', $productIdToExclude);
                $this->removeItemByKey($quoteItem->getId());
            }
        }
    }
}
