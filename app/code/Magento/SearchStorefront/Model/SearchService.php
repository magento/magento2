<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SearchStorefront\Model;

use Magento\SearchStorefront\DataProvider\Product\LayeredNavigation\LayerBuilderInterface;
use Magento\SearchStorefront\DataProvider\Product\SearchCriteria\SearchCriteriaBuilderInterface;
use Magento\SearchStorefrontApi\Api\Data\ProductSearchRequestInterface;
use Magento\SearchStorefrontApi\Api\Data\ProductsSearchResultMapper;
use Magento\SearchStorefrontApi\Api\Data\ProductsSearchResultInterface;
use Magento\SearchStorefrontApi\Api\SearchServerInterface;
use Magento\SearchStorefrontApi\Api\Data\ProductsSearchResult;
use Magento\SearchStorefrontSearch\Api\SearchInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Class for fulltext search
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.NPathComplexity)
 */
class SearchService implements SearchServerInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var SearchCriteriaBuilderInterface
     */
    private $searchCriteriaBuilder;

    /**
     * @var SearchInterface
     */
    private $search;

    /**
     * @var StoreInterface
     */
    private $store;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var LayerBuilderInterface
     */
    private $layerBuilder;

    /**
     * @var ProductsSearchResultMapper
     */
    private $mapper;

    /**
     * @param StoreInterface                  $store
     * @param StoreManagerInterface           $storeManager
     * @param SearchCriteriaBuilderInterface  $searchCriteriaBuilder
     * @param SearchInterface                 $search
     * @param LayerBuilderInterface           $layerBuilder
     * @param ProductsSearchResultMapper $mapper
     * @param LoggerInterface                 $logger
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        StoreInterface $store,
        StoreManagerInterface $storeManager,
        SearchCriteriaBuilderInterface $searchCriteriaBuilder,
        SearchInterface $search,
        LayerBuilderInterface $layerBuilder,
        ProductsSearchResultMapper $mapper,
        LoggerInterface $logger
    ) {
        $this->logger = $logger;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->search = $search;
        $this->store = $store;
        $this->storeManager = $storeManager;
        $this->layerBuilder = $layerBuilder;
        $this->mapper = $mapper;
    }

    /**
     * Get requested products
     *
     * @param ProductSearchRequestInterface $request
     * @return ProductsSearchResultInterface
     */
    public function searchProducts(
        ProductSearchRequestInterface $request
    ): ProductsSearchResultInterface {
        $result = new ProductsSearchResult();

        if (empty($request->getStore()) || $request->getStore() === null) {
            throw new \InvalidArgumentException('Store code is not present in request.');
        }

        $this->store
            ->setId($request->getStore())
            ->setCustomerGroupId($request->getCustomerGroupId()??0)
            ->load();

        $this->storeManager->setCurrentStore($this->store);

        if (empty($request->getStore()) || $request->getStore() === null) {
            return $result;
        }

        $searchCriteria = $this->searchCriteriaBuilder->build($request);
        $raw = $this->search->search($searchCriteria);

        $this->mapper->setData([
            'total_count' => $raw->getTotalCount(),
            'items' => $this->getItems($raw->getItems()),
            'facets' => $this->layerBuilder->build($raw->getAggregations(), 1),
            'page_info' => [
                'page_size' => $raw->getSearchCriteria()->getPageSize(),
                'current_page' => $raw->getSearchCriteria()->getCurrentPage() + 1,
                'total_pages' => (int) ceil($raw->getTotalCount() / $raw->getSearchCriteria()->getPageSize())
            ]
        ]);

        return $this->mapper->build();
    }

    private function getItems($items)
    {
        $productIds = [];
        foreach ($items ?? [] as $item) {
            $productIds[] = $item->getId();
        }
        return $productIds;
    }
}
