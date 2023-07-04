<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Model\Cart;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Quote\Model\Quote\Config;

/**
 * Cart reader product loader.
 */
class ProductReader implements ProductReaderInterface
{
    /**
     * @var ProductCollectionFactory
     */
    private $productCollectionFactory;

    /**
     * @var ProductInterface[]
     */
    private $productsBySku;

    /**
     * @var Config
     */
    private $quoteConfig;

    /**
     * @var Collection
     */
    private $productCollection;

    /**
     * @param ProductCollectionFactory $productCollectionFactory
     * @param Config $quoteConfig
     */
    public function __construct(
        ProductCollectionFactory $productCollectionFactory,
        Config $quoteConfig
    ) {
        $this->productCollectionFactory = $productCollectionFactory;
        $this->quoteConfig = $quoteConfig;
    }

    /**
     * @inheritDoc
     */
    public function loadProducts(array $skus, int $storeId): void
    {
        $this->productCollection = $this->productCollectionFactory->create();

        $this->productCollection->addAttributeToSelect($this->quoteConfig->getProductAttributes());
        $this->productCollection->setStoreId($storeId);
        $this->productCollection->addStoreFilter($storeId);
        $this->productCollection->addFieldToFilter(ProductInterface::SKU, ['in' => $skus]);
        $this->productCollection->joinAttribute('status', 'catalog_product/status', 'entity_id', null, 'inner');
        $this->productCollection->joinAttribute('visibility', 'catalog_product/visibility', 'entity_id', null, 'inner');
        $this->productCollection->load();
        foreach ($this->productCollection->getItems() as $productItem) {
            $this->productsBySku[$productItem->getData(ProductInterface::SKU)] = $productItem;
        }
    }

    /**
     * @inheritDoc
     */
    public function getProductBySku(string $sku) : ?ProductInterface
    {
        return $this->productsBySku[$sku] ?? null;
    }
}
