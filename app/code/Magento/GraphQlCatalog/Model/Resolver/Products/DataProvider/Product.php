<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GraphQlCatalog\Model\Resolver\Products\DataProvider;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Option;
use Magento\Catalog\Model\Product\TierPrice;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Data\SearchResultInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\Webapi\ServiceOutputProcessor;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Catalog\Api\Data\ProductSearchResultsInterfaceFactory;
use Magento\GraphQl\Model\EntityAttributeList;

/**
 * Product field data provider, used for GraphQL resolver processing.
 */
class Product
{
    /**
     * @var ServiceOutputProcessor
     */
    private $serviceOutputProcessor;

    /**
     * @var MediaGalleryEntries
     */
    private $mediaGalleryResolver;

    /**
     * @var SerializerInterface
     */
    private $jsonSerializer;

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
     * @param ServiceOutputProcessor $serviceOutputProcessor
     * @param MediaGalleryEntries $mediaGalleryResolver
     * @param SerializerInterface $jsonSerializer
     * @param CollectionFactory $collectionFactory
     * @param JoinProcessorInterface $joinProcessor
     * @param CollectionProcessorInterface $collectionProcessor
     * @param ProductSearchResultsInterfaceFactory $searchResultsFactory
     */
    public function __construct(
        ServiceOutputProcessor $serviceOutputProcessor,
        MediaGalleryEntries $mediaGalleryResolver,
        SerializerInterface $jsonSerializer,
        CollectionFactory $collectionFactory,
        JoinProcessorInterface $joinProcessor,
        CollectionProcessorInterface $collectionProcessor,
        ProductSearchResultsInterfaceFactory $searchResultsFactory
    ) {
        $this->serviceOutputProcessor = $serviceOutputProcessor;
        $this->mediaGalleryResolver = $mediaGalleryResolver;
        $this->jsonSerializer = $jsonSerializer;
        $this->collectionFactory = $collectionFactory;
        $this->joinProcessor = $joinProcessor;
        $this->collectionProcessor = $collectionProcessor;
        $this->searchResultsFactory = $searchResultsFactory;
    }

    /**
     * Transform single product data from object to in array format
     *
     * @param \Magento\Catalog\Api\Data\ProductInterface $productObject
     * @return array|null
     */
    public function processProduct(\Magento\Catalog\Api\Data\ProductInterface $productObject)
    {
        /** @var \Magento\Catalog\Model\Product  $productObject */
        $productArray = $productObject->getData();
        $productArray['id'] = $productArray['entity_id'];
        unset($productArray['entity_id']);
        $productArray['media_gallery_entries'] = $productObject->getMediaGalleryEntries();
        if (isset($productArray['media_gallery_entries'])) {
            foreach ($productArray['media_gallery_entries'] as $key => $entry) {
                if ($entry->getExtensionAttributes() && $entry->getExtensionAttributes()->getVideoContent()) {
                    $productArray['media_gallery_entries'][$key]['video_content']
                        = $entry->getExtensionAttributes()->getVideoContent()->getData();
                }
            }
        }
        if (isset($productArray['options'])) {
            /** @var Option $option */
            foreach ($productArray['options'] as $key => $option) {
                $productArray['options'][$key] = $option->getData();
                $productArray['options'][$key]['product_sku'] = $option->getProductSku();
                $values = $option->getValues() ?: [];
                /** @var Option\Value $value */
                foreach ($values as $value) {
                    $productArray['options'][$key]['values'][] = $value->getData();
                }
            }
        }
        $tierPrices = $productObject->getTierPrices();
        if ($tierPrices) {
            /** @var TierPrice $tierPrice */
            foreach ($tierPrices as $tierPrice) {
                $productArray['tier_prices'][] = $tierPrice->getData();
            }
        } else {
            $productArray['tier_prices'] = null;
        }

        return $productArray;
    }

    /**
     * Gets list of product data with full data set
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return SearchResultInterface
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria)
    {
        /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $collection */
        $collection = $this->collectionFactory->create();
        $this->joinProcessor->process($collection);

        $collection->addAttributeToSelect('*');
        $collection->joinAttribute('status', 'catalog_product/status', 'entity_id', null, 'inner');
        $collection->joinAttribute('visibility', 'catalog_product/visibility', 'entity_id', null, 'inner');

        $this->collectionProcessor->process($searchCriteria, $collection);

        $collection->load();

        $collection->addCategoryIds();
        $collection->addFinalPrice();
        $collection->addMediaGalleryData();
        $collection->addMinimalPrice();
        $collection->addPriceData();
        $collection->addWebsiteNamesToResult();
        $collection->addOptionsToResult();
        $collection->addTaxPercents();
        $collection->addWebsiteNamesToResult();
        $searchResult = $this->searchResultsFactory->create();
        $searchResult->setSearchCriteria($searchCriteria);
        $searchResult->setItems($collection->getItems());
        $searchResult->setTotalCount($collection->getSize());

        return $searchResult;
    }
}
