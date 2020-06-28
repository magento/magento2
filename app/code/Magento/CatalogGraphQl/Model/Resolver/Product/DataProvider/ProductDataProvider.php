<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Resolver\Product\DataProvider;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\ResourceModel\Product as ProductResourceModel;

/**
 * Product data provider, used for GraphQL resolver processing.
 */
class ProductDataProvider implements ProductDataProviderInterface
{
    /**
     * @var ProductResourceModel
     */
    private $productResourceModel;

    /**
     * @var ProductFactory
     */
    private $productFactory;

    /**
     * ProductDataProvider constructor.
     *
     * @param ProductResourceModel $productResourceModel
     * @param ProductFactory $productFactory
     */
    public function __construct(
        ProductResourceModel $productResourceModel,
        ProductFactory $productFactory
    ) {
        $this->productResourceModel = $productResourceModel;
        $this->productFactory = $productFactory;
    }

    /**
     * Get list of products by IDs with full data set
     *
     * @param array $productIds
     * @param array $attributeCodes
     * @return ProductInterface[]|Product[]
     */
    public function getProductByIds(array $productIds, array $attributeCodes): array
    {
        $products = [];

        foreach ($productIds as $productId) {
            /** @var Product $product */
            $product = $this->productFactory->create();
            $this->productResourceModel->load($product, $productId, $attributeCodes);
            $products[] = $product;
        }

        return $products;
    }
}
