<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types = 1);

namespace Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Catalog\Api\Data\ProductSearchResultsInterfaceFactory;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\Framework\App\ObjectManager;

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
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        JoinProcessorInterface $joinProcessor,
        CollectionProcessorInterface $collectionProcessor,
        ProductSearchResultsInterfaceFactory $searchResultsFactory,
        \Magento\Catalog\Model\Layer\Resolver $layerResolver,
        \Magento\Catalog\Model\ProductRepository $productRepository
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->joinProcessor = $joinProcessor;
        $this->collectionProcessor = $collectionProcessor;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->layerResolver = $layerResolver;
        $this->productRepository = $productRepository;
    }

    /**
     * Gets list of product data with full data set
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return SearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria) : SearchResultsInterface
    {
        if (!$this->searchResult) {

//            /** @var \Magento\CatalogSearch\Model\Advanced $advancedSearch */
//            $advancedSearch = ObjectManager::getInstance()->get(\Magento\CatalogSearch\Model\Advanced::class);
//            /** @var \Magento\Framework\Api\Search\FilterGroup $filterGroup */
//            foreach ($searchCriteria->getFilterGroups() as $filterGroup) {
//                /** @var \Magento\Framework\Api\Filter $filter */
//                foreach ($filterGroup as $filter) {
//                    $advancedSearch->addFilters(
//                        [
//                            \Magento\Framework\Api\Filter::KEY_FIELD
//                        ]
//                    );
//                }
//            }


//            /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $collection */
//            $collection = $this->layerResolver->get()->getProductCollection();
//            $this->joinProcessor->process($collection);
//
//            $collection->addAttributeToSelect('*');
//            $collection->joinAttribute('status', 'catalog_product/status', 'entity_id', null, 'inner');
//            $collection->joinAttribute('visibility', 'catalog_product/visibility', 'entity_id', null, 'inner');
//
//            $this->collectionProcessor->process($searchCriteria, $collection);
//
//            $collection->load();
//
//            $collection->addCategoryIds();
//            $collection->addFinalPrice();
//            $collection->addMediaGalleryData();
//            $collection->addMinimalPrice();
//            $collection->addPriceData();
//            $collection->addWebsiteNamesToResult();
//            $collection->addOptionsToResult();
//            $collection->addTaxPercents();
//            $collection->addWebsiteNamesToResult();
//            $this->searchResult = $this->searchResultsFactory->create();
//            $this->searchResult->setSearchCriteria($searchCriteria);
//            $this->searchResult->setItems($collection->getItems());
//            $this->searchResult->setTotalCount($collection->getSize());
            $this->searchResult = $this->productRepository->getList($searchCriteria);
        }
        return $this->searchResult;
    }
}
