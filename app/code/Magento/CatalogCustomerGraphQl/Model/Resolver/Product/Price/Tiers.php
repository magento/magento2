<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogCustomerGraphQl\Model\Resolver\Product\Price;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\Customer\Model\GroupManagement;
use Magento\Catalog\Api\Data\ProductTierPriceInterface;
use Magento\CatalogGraphQl\Model\Resolver\Product\Price\ProviderPool as PriceProviderPool;

/**
 * Get product tier price information
 */
class Tiers
{
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var ProductResource
     */
    private $productResource;

    /**
     * @var PriceProviderPool
     */
    private $priceProviderPool;

    /**
     * @var bool
     */
    private $loaded = false;

    /**
     * @var int
     */
    private $customerGroupId = GroupManagement::CUST_GROUP_ALL;

    /**
     * @var array
     */
    private $filterProductIds = [];

    /**
     * @var array
     */
    private $products = [];

    /**
     * @param CollectionFactory $collectionFactory
     * @param ProductResource $productResource
     * @param PriceProviderPool $priceProviderPool
     * @param int $customerGroupId
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        ProductResource $productResource,
        PriceProviderPool $priceProviderPool,
        $customerGroupId
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->productResource = $productResource;
        $this->priceProviderPool = $priceProviderPool;
        $this->customerGroupId = $customerGroupId;
    }

    /**
     * Add product ID to collection filter
     *
     * @param int $productId
     */
    public function addProductFilter(int $productId): void
    {
        $this->filterProductIds[] = $productId;
    }

    /**
     * Get tier prices for product by ID
     *
     * @param int $productId
     * @return ProductTierPriceInterface[]|null
     */
    public function getProductTierPrices($productId): ?array
    {
        if (!$this->isLoaded()) {
            $this->load();
        }

        if (empty($this->products[$productId])) {
            return null;
        }
        return $this->products[$productId]->getTierPrices();
    }

    /**
     * Get product regular price by ID
     *
     * @param int $productId
     * @return float|null
     */
    public function getProductRegularPrice($productId): ?float
    {
        if (!$this->isLoaded()) {
            $this->load();
        }

        if (empty($this->products[$productId])) {
            return null;
        }
        $product = $this->products[$productId];
        $priceProvider = $this->priceProviderPool->getProviderByProductType($product->getTypeId());
        return $priceProvider->getRegularPrice($product)->getValue();
    }

    /**
     * Check if collection has been loaded
     *
     * @return bool
     */
    public function isLoaded(): bool
    {
        $numFilterProductIds = count(array_unique($this->filterProductIds));
        if ($numFilterProductIds > count($this->products)) {
            //New products were added to the filter after load, so we should reload
            return false;
        }
        return $this->loaded;
    }

    /**
     * Load product collection
     */
    private function load(): void
    {
        $this->loaded = false;

        $productIdField = $this->productResource->getEntityIdField();
        /** @var Collection $productCollection */
        $productCollection = $this->collectionFactory->create();
        $productCollection->addFieldToFilter($productIdField, ['in' => $this->filterProductIds]);
        $productCollection->addAttributeToSelect('price');
        $productCollection->addAttributeToSelect('price_type');
        $productCollection->load();
        $productCollection->addTierPriceDataByGroupId($this->customerGroupId);

        $this->setProducts($productCollection);
        $this->loaded = true;
    }

    /**
     * Set products from collection
     *
     * @param Collection $productCollection
     */
    private function setProducts(Collection $productCollection): void
    {
        $this->products = [];

        foreach ($productCollection as $product) {
            $this->products[$product->getId()] = $product;
        }

        $missingProducts = array_diff($this->filterProductIds, array_keys($this->products));
        foreach (array_unique($missingProducts) as $missingProductId) {
            $this->products[$missingProductId] = null;
        }
    }
}
