<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GraphQlCatalog\Model\Resolver\Products\DataProvider;

use Magento\Catalog\Api\ProductRepositoryInterface;
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
     * @var ProductRepositoryInterface
     */
    private $productRepository;

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
     * @var EntityAttributeList
     */
    private $entityAttributeList;

    /**
     * @var \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable\Attribute\Collection
     */
    private $configurable;

    /**
     * @param ProductRepositoryInterface $productRepository
     * @param ServiceOutputProcessor $serviceOutputProcessor
     * @param MediaGalleryEntries $mediaGalleryResolver
     * @param SerializerInterface $jsonSerializer
     * @param CollectionFactory $collectionFactory
     * @param JoinProcessorInterface $joinProcessor
     * @param CollectionProcessorInterface $collectionProcessor
     * @param ProductSearchResultsInterfaceFactory $searchResultsFactory
     * @param EntityAttributeList $entityAttributeList
     * @param \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable\Attribute\Collection $collection
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        ServiceOutputProcessor $serviceOutputProcessor,
        MediaGalleryEntries $mediaGalleryResolver,
        SerializerInterface $jsonSerializer,
        CollectionFactory $collectionFactory,
        JoinProcessorInterface $joinProcessor,
        CollectionProcessorInterface $collectionProcessor,
        ProductSearchResultsInterfaceFactory $searchResultsFactory,
        EntityAttributeList $entityAttributeList,
        \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable\Attribute\Collection $collection
    ) {
        $this->productRepository = $productRepository;
        $this->serviceOutputProcessor = $serviceOutputProcessor;
        $this->mediaGalleryResolver = $mediaGalleryResolver;
        $this->jsonSerializer = $jsonSerializer;
        $this->collectionFactory = $collectionFactory;
        $this->joinProcessor = $joinProcessor;
        $this->collectionProcessor = $collectionProcessor;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->entityAttributeList = $entityAttributeList;
        $this->configurable = $collection;
    }

    /**
     * Get product data by Sku
     *
     * @param string $sku
     * @return array|null
     */
    public function getProduct(string $sku)
    {
        try {
            $productObject = $this->productRepository->get($sku);
        } catch (NoSuchEntityException $e) {
            // No error should be thrown, null result should be returned
            return null;
        }
        return $this->processProduct($productObject);
    }

    /**
     * Get product data by Id
     *
     * @param int $productId
     * @return array|null
     */
    public function getProductById(int $productId)
    {
        try {
            $productObject = $this->productRepository->getById($productId);
        } catch (NoSuchEntityException $e) {
            // No error should be thrown, null result should be returned
            return null;
        }
        return $this->processProduct($productObject);
    }

    /**
     * Transform single product data from object to in array format
     *
     * @param \Magento\Catalog\Api\Data\ProductInterface $productObject
     * @return array|null
     */
    public function processProduct(\Magento\Catalog\Api\Data\ProductInterface $productObject)
    {
//        $productObject = $this->productRepository->get($productObject->getSku());
        $product = $this->serviceOutputProcessor->process(
            $productObject,
            ProductRepositoryInterface::class,
            'get'
        );
        if (isset($product['extension_attributes'])) {
            $product = array_merge($product, $product['extension_attributes']);
        }
        $customAttributes = [];
        if (isset($product['custom_attributes'])) {
            foreach ($product['custom_attributes'] as $attribute) {
                $isArray = false;
                if (is_array($attribute['value'])) {
                    $isArray = true;
                    foreach ($attribute['value'] as $attributeValue) {
                        if (is_array($attributeValue)) {
                            $customAttributes[$attribute['attribute_code']] = $this->jsonSerializer->serialize(
                                $attribute['value']
                            );
                            continue;
                        }
                        $customAttributes[$attribute['attribute_code']] = implode(',', $attribute['value']);
                        continue;
                    }
                }
                if ($isArray) {
                    continue;
                }
                $customAttributes[$attribute['attribute_code']] = $attribute['value'];
            }
        }
        $product = array_merge($product, $customAttributes);
        $product = array_merge($product, $product['product_links']);
        $product['media_gallery_entries']
            = $this->mediaGalleryResolver->getMediaGalleryEntries($productObject->getSku());

        return $product;
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
