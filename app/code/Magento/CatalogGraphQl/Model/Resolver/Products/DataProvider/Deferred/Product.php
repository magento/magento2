<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\Deferred;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\Product as ProductDataProvider;
use Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\Product\FormatterInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;

/**
 * Deferred resolver for product data.
 */
class Product
{
    /**
     * @var ProductDataProvider
     */
    private $productDataProvider;

    /**
     * @var FormatterInterface
     */
    private $formatter;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var string[]
     */
    private $productSkus = [];

    /**
     * @var array
     */
    private $productList = [];

    /**
     * @param ProductDataProvider $productDataProvider
     * @param FormatterInterface $formatter
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        ProductDataProvider $productDataProvider,
        FormatterInterface $formatter,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->productDataProvider = $productDataProvider;
        $this->formatter = $formatter;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * Add product sku to result set at fetch time.
     *
     * @param string $sku
     * @return void
     */
    public function addProductSku(string $sku) : void
    {
        if (!in_array($sku, $this->productSkus)) {
            $this->productSkus[] = $sku;
        }
    }

    /**
     * Add product skus to result set at fetch time.
     *
     * @param array $skus
     * @return void
     */
    public function addProductSkus(array $skus) : void
    {
        foreach ($skus as $sku) {
            if (in_array($sku, $this->productSkus)) {
                continue;
            }

            $this->productSkus[] = $sku;
        }
    }

    /**
     * Get product from result set.
     *
     * @param string $sku
     * @return array|null
     */
    public function getProductBySku(string $sku) : ?array
    {
        $products = $this->fetch();

        if (!isset($products[$sku])) {
            return null;
        }

        return $products[$sku];
    }

    /**
     * Fetch product data and return in array format. Keys for products will be their skus.
     *
     * @return array
     */
    private function fetch() : array
    {
        if (empty($this->productSkus) || !empty($this->productList)) {
            return $this->productList;
        }

        $this->searchCriteriaBuilder->addFilter(ProductInterface::SKU, $this->productSkus, 'in');
        $result = $this->productDataProvider->getList($this->searchCriteriaBuilder->create());

        /** @var \Magento\Catalog\Model\Product $product */
        foreach ($result->getItems() as $product) {
            $this->productList[$product->getSku()] = $this->formatter->format($product);
        }

        return $this->productList;
    }
}
