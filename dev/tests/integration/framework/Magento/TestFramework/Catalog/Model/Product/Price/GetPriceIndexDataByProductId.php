<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestFramework\Catalog\Model\Product\Price;

use Magento\Catalog\Model\Indexer\Product\Price\PriceTableResolver;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\Customer\Model\Indexer\CustomerGroupDimensionProvider;
use Magento\Framework\Indexer\DimensionFactory;
use Magento\Store\Model\Indexer\WebsiteDimensionProvider;

/**
 * Search and return price data from price index table.
 */
class GetPriceIndexDataByProductId
{
    /**
     * @var ProductResource
     */
    private $productResource;

    /**
     * @var PriceTableResolver
     */
    private $priceTableResolver;

    /**
     * @var DimensionFactory
     */
    private $dimensionFactory;

    /**
     * @param ProductResource $productResource
     * @param PriceTableResolver $priceTableResolver
     * @param DimensionFactory $dimensionFactory
     */
    public function __construct(
        ProductResource $productResource,
        PriceTableResolver $priceTableResolver,
        DimensionFactory $dimensionFactory
    ) {
        $this->productResource = $productResource;
        $this->priceTableResolver = $priceTableResolver;
        $this->dimensionFactory = $dimensionFactory;
    }

    /**
     * Returns price data by product id.
     *
     * @param int $productId
     * @param int $groupId
     * @param int $websiteId
     * @return array
     */
    public function execute(int $productId, int $groupId, int $websiteId): array
    {
        $tableName = $this->priceTableResolver->resolve(
            'catalog_product_index_price',
            [
                $this->dimensionFactory->create(WebsiteDimensionProvider::DIMENSION_NAME, (string)$websiteId),
                $this->dimensionFactory->create(CustomerGroupDimensionProvider::DIMENSION_NAME, (string)$groupId),
            ]
        );

        $select = $this->productResource->getConnection()->select()
            ->from($tableName)
            ->where('entity_id = ?', $productId)
            ->where('customer_group_id = ?', $groupId)
            ->where('website_id = ?', $websiteId);

        return $this->productResource->getConnection()->fetchAll($select);
    }
}
