<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProductGraphQl\Model\Options;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\ProductRepository;
use Magento\ConfigurableProduct\Helper\Data;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable\Attribute;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable\Attribute\Collection
    as AttributeCollection;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable\Attribute\CollectionFactory;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\EntityManager\MetadataPool;

/**
 * Collection for fetching options for all configurable options pulled back in result set.
 */
class Collection
{
    /**
     * @var CollectionFactory
     */
    private $attributeCollectionFactory;

    /**
     * @var ProductFactory
     */
    private $productFactory;

    /**
     * @var ProductRepository
     */
    private $productRepository;

    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @var Data
     */
    private $configurableProductHelper;

    /**
     * @var Metadata
     */
    private $optionsMetadata;

    /**
     * @var SelectionUidFormatter
     */
    private $selectionUidFormatter;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var int[]
     */
    private $productIds = [];

    /**
     * @var array
     */
    private $attributeMap = [];

    /**
     * @param CollectionFactory $attributeCollectionFactory
     * @param ProductFactory $productFactory
     * @param ProductRepository $productRepository
     * @param MetadataPool $metadataPool
     * @param Data $configurableProductHelper
     * @param Metadata $optionsMetadata
     * @param SelectionUidFormatter $selectionUidFormatter
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        CollectionFactory $attributeCollectionFactory,
        ProductFactory $productFactory,
        ProductRepository $productRepository,
        MetadataPool $metadataPool,
        Data $configurableProductHelper,
        Metadata $optionsMetadata,
        SelectionUidFormatter $selectionUidFormatter,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->attributeCollectionFactory = $attributeCollectionFactory;
        $this->productFactory = $productFactory;
        $this->productRepository = $productRepository;
        $this->metadataPool = $metadataPool;
        $this->configurableProductHelper = $configurableProductHelper;
        $this->optionsMetadata = $optionsMetadata;
        $this->selectionUidFormatter = $selectionUidFormatter;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder ??
            ObjectManager::getInstance()->get(SearchCriteriaBuilder::class);
    }

    /**
     * Add product id to attribute collection filter.
     *
     * @param int $productId
     */
    public function addProductId(int $productId) : void
    {
        if (!in_array($productId, $this->productIds)) {
            $this->productIds[] = $productId;
        }
    }

    /**
     * Retrieve attributes for given product id or empty array
     *
     * @param int $productId
     * @return array
     */
    public function getAttributesByProductId(int $productId) : array
    {
        $attributes = $this->fetch();

        if (!isset($attributes[$productId])) {
            return [];
        }

        return $attributes[$productId];
    }

    /**
     * Fetch attribute data
     *
     * @return array
     */
    private function fetch() : array
    {
        if (empty($this->productIds) || !empty($this->attributeMap)) {
            return $this->attributeMap;
        }

        $linkField = $this->metadataPool->getMetadata(ProductInterface::class)->getLinkField();
        /** @var AttributeCollection $attributeCollection */
        $attributeCollection = $this->attributeCollectionFactory->create();
        foreach ($this->productIds as $id) {
            /** @var Product $product */
            $product = $this->productFactory->create();
            $product->setData($linkField, $id);
            $attributeCollection->setProductFilter($product);
        }

        $products = $this->getProducts($this->productIds);

        /** @var Attribute $attribute */
        foreach ($attributeCollection->getItems() as $attribute) {
            $productId = (int)$attribute->getProductId();
            if (!isset($this->attributeMap[$productId])) {
                $this->attributeMap[$productId] = [];
            }

            $attributeData = $attribute->getData();
            $this->attributeMap[$productId][$attribute->getId()] = $attribute->getData();
            $this->attributeMap[$productId][$attribute->getId()]['id'] = $attribute->getId();
            $this->attributeMap[$productId][$attribute->getId()]['attribute_id_v2']
                = $attribute->getProductAttribute()->getAttributeId();
            $this->attributeMap[$productId][$attribute->getId()]['attribute_code']
                = $attribute->getProductAttribute()->getAttributeCode();
            $this->attributeMap[$productId][$attribute->getId()]['values'] = $attributeData['options'];
            $this->attributeMap[$productId][$attribute->getId()]['label']
                = $attribute->getProductAttribute()->getStoreLabel();

            if (isset($products[$productId])) {
                $options = $this->configurableProductHelper->getOptions(
                    $products[$productId],
                    $this->optionsMetadata->getAllowProducts($products[$productId])
                );
                foreach ($attributeData['options'] as $index => $value) {
                    $this->attributeMap[$productId][$attribute->getId()]['values'][$index]['uid']
                        = $this->selectionUidFormatter->encode(
                            (int)$attribute->getAttributeId(),
                            (int)$value['value_index']
                        );
                    $this->attributeMap[$productId][$attribute->getId()]['values'][$index]
                        ['is_available_for_selection'] =
                        isset($options[$attribute->getAttributeId()][$value['value_index']])
                        && $options[$attribute->getAttributeId()][$value['value_index']];
                }
            }
        }

        return $this->attributeMap;
    }

    /**
     * Load products by link field ids
     *
     * @param int[] $productLinkIds
     * @return ProductInterface[]
     */
    private function getProducts($productLinkIds)
    {
        $linkField = $this->metadataPool->getMetadata(ProductInterface::class)->getLinkField();
        $this->searchCriteriaBuilder->addFilter($linkField, $productLinkIds, 'in');
        $searchCriteria = $this->searchCriteriaBuilder->create();
        $products = $this->productRepository->getList($searchCriteria)->getItems();
        $productsLinkFieldMap = [];
        foreach ($products as $product) {
            $productsLinkFieldMap[$product->getData($linkField)] = $product;
        }
        return $productsLinkFieldMap;
    }
}
