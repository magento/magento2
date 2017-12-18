<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GraphQlCatalog\Model\Resolver\Products\Query;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\GraphQlCatalog\Model\Resolver\Products\DataProvider\Product;
use Magento\GraphQlCatalog\Model\Resolver\Products\SearchResult;
use Magento\GraphQlCatalog\Model\Resolver\Products\SearchResultFactory;

/**
 * Retrieve filtered product data based off given search criteria in a format that GraphQL can interpret.
 */
class Filter
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var SearchResultFactory
     */
    private $searchResultFactory;

    /**
     * @var Product
     */
    private $productDataProvider;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product
     */
    private $productResource;

    /**
     * @param ProductRepositoryInterface $productRepository
     * @param SearchResultFactory $searchResultFactory
     * @param Product $productDataProvider
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        SearchResultFactory $searchResultFactory,
        Product $productDataProvider,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Catalog\Model\ResourceModel\Product $productResource
    ) {
        $this->productRepository = $productRepository;
        $this->searchResultFactory = $searchResultFactory;
        $this->productDataProvider = $productDataProvider;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->productResource = $productResource;
    }

    /**
     * Filter catalog product data based off given search criteria
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return SearchResult
     */
    public function getResult(SearchCriteriaInterface $searchCriteria)
    {
        $realPageSize = $searchCriteria->getPageSize();
        $realCurrentPage = $searchCriteria->getCurrentPage();
        // Current page must be set to 0 and page size to max for search to grab all ID's as temporary workaround for
        // inaccurate search
        $searchCriteria->setPageSize(PHP_INT_MAX);
        $searchCriteria->setCurrentPage(1);
        $products = $this->productDataProvider->getList($searchCriteria);
        $productArray = [];
        $configurableProducts = [];
        $childrenIds = [];
        $searchCriteria->setPageSize($realPageSize);
        $searchCriteria->setCurrentPage($realCurrentPage);
        $paginatedProducts = $this->paginateList($products->getItems(), $searchCriteria);
        /** @var ProductInterface $product */
        foreach ($paginatedProducts as $product) {
            $productData = $this->productDataProvider->processProduct($product);
            if ($product->getTypeId() === Configurable::TYPE_CODE) {
                $extensionAttributes = $product->getExtensionAttributes();
                $productData['configurable_product_options'] = $extensionAttributes->getConfigurableProductOptions();
                $productData['configurable_product_links'] = $extensionAttributes->getConfigurableProductLinks();
                $childrenIds = array_merge($childrenIds, $productData['configurable_product_links']);
                $formattedLinks = [];
                foreach ($productData['configurable_product_links'] as $configurable_product_link) {
                    $formattedLinks[$configurable_product_link] = null;
                }
                $productData['configurable_product_links'] = $formattedLinks;
                $configurableProducts[] = $productData;
            } else {
                $productArray[] = $productData;
            }
        }

        if (!empty($configurableProducts)) {
            $this->searchCriteriaBuilder->addFilter('entity_id', $childrenIds, 'in');
            $childProducts = $this->productDataProvider->getList($this->searchCriteriaBuilder->create());
            /** @var \Magento\Catalog\Model\Product $childProduct */
            foreach ($childProducts->getItems() as $childProduct) {
                $childData = $this->productDataProvider->processProduct($childProduct);
                $childId = (int)$childProduct->getId();
                foreach ($configurableProducts as $key => $configurableProduct) {
                    if (array_key_exists($childId, $configurableProduct['configurable_product_links'])) {
                        $configurableProducts[$key]['configurable_product_links'][$childId] = $childData;
                        $categoryLinks = $this->productResource->getCategoryIds($childProduct);
                        foreach ($categoryLinks as $key => $link) {
                            $configurableProducts[$key]['configurable_product_links'][$childId]['category_links'][] =
                                ['position' => $key, 'category_id' => $link];
                        }
                    }
                }
            }
        }

        $productArray = array_merge($productArray, $configurableProducts);

        return $this->searchResultFactory->create($products->getTotalCount(), $productArray);
    }

    /**
     * Paginates array of Ids pulled back in search based off search criteria and total count.
     *
     * This function and its usages should be removed after MAGETWO-85611 is resolved.
     *
     * @param array $ids
     * @param SearchCriteriaInterface $searchCriteria
     * @return int[]
     */
    private function paginateList(array $ids, SearchCriteriaInterface $searchCriteria)
    {
        $length = $searchCriteria->getPageSize();
        // Search starts pages from 0
        $offset = $length * ($searchCriteria->getCurrentPage() - 1);
        return array_slice($ids, $offset, $length);
    }
}
