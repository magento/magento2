<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types = 1);

namespace Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider;

use Magento\Catalog\Model\ResourceModel\CategoryProduct;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Catalog\Api\Data\ProductSearchResultsInterfaceFactory;
use Magento\Framework\Api\SearchResultsInterface;

/**
 * Product field data provider, used for GraphQL resolver processing.
 */
class Product
{
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var JoinProcessorInterface
     */
    private $joinProcessor;

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @var ProductSearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    /**
     * @var CategoryProduct
     */
    private $categoryProduct;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var \Magento\Catalog\Model\Layer\Resolver
     */
    private $layerResolver;

    /**
     * @var \Magento\Catalog\Api\Data\ProductSearchResultsInterface
     */
    private $searchResult;

    /**
     * @var \Magento\Catalog\Model\ProductRepository
     */
    private $productRepository;

    /**
     * @param CollectionFactory $collectionFactory
     * @param JoinProcessorInterface $joinProcessor
     * @param CollectionProcessorInterface $collectionProcessor
     * @param ProductSearchResultsInterfaceFactory $searchResultsFactory
     * @param CategoryProduct $categoryProduct
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        JoinProcessorInterface $joinProcessor,
        CollectionProcessorInterface $collectionProcessor,
        ProductSearchResultsInterfaceFactory $searchResultsFactory,
        CategoryProduct $categoryProduct,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Catalog\Model\Layer\Resolver $layerResolver,
        \Magento\Catalog\Model\ProductRepository $productRepository
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->joinProcessor = $joinProcessor;
        $this->collectionProcessor = $collectionProcessor;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->categoryProduct = $categoryProduct;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->layerResolver = $layerResolver;
        $this->productRepository = $productRepository;
    }

    /**
     * Gets list of product data with full data set. Adds eav attributes to result set from passed in array
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @param string[] $attributes
     * @return SearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria, array $attributes = []) : SearchResultsInterface
    {
        /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $collection */
        $collection = $this->collectionFactory->create();
        $this->joinProcessor->process($collection);

        foreach ($attributes as $attributeCode) {
            $collection->addAttributeToSelect($attributeCode);
        }
        $collection->addAttributeToSelect('special_price');
        $collection->addAttributeToSelect('special_price_from');
        $collection->addAttributeToSelect('special_price_to');
        $collection->addAttributeToSelect('tax_class_id');
        $collection->joinAttribute('status', 'catalog_product/status', 'entity_id', null, 'inner');
        $collection->joinAttribute('visibility', 'catalog_product/visibility', 'entity_id', null, 'inner');

        $this->collectionProcessor->process($searchCriteria, $collection);
        $collection->addWebsiteNamesToResult();
        $collection->load();

        // Methods that perform extra fetches
        $collection->addCategoryIds();
        $collection->addMediaGalleryData();
        $collection->addOptionsToResult();

        $searchResult = $this->searchResultsFactory->create();
        $searchResult->setSearchCriteria($searchCriteria);
        $searchResult->setItems($collection->getItems());
        $searchResult->setTotalCount($collection->getSize());
        return $searchResult;
    }
}
