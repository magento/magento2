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
     * @var bool
     */
    private $loaded = false;

    /**
     * @var int
     */
    private $customerGroupId =  GroupManagement::CUST_GROUP_ALL;

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
     * @param int $customerGroupId
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        ProductResource $productResource,
        $customerGroupId
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->productResource = $productResource;
        $this->customerGroupId = $customerGroupId;
    }

    /**
     * Add product ID to collection filter
     *
     * @param int $productId
     */
    public function addProductFilter($productId): void
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
     * Check if collection has been loaded
     *
     * @return bool
     */
    public function isLoaded(): bool
    {
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
        $productCollection->addTierPriceDataByGroupId($this->customerGroupId);

        foreach ($productCollection as $product) {
            $this->products[$product->getId()] = $product;
        }

        $this->loaded = true;
    }
}
