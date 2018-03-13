<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProductGraphQl\Model\Plugin\Model\Resolver\Products\DataProvider;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable\Attribute\Collection
    as AttributeCollection;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable\Product\Collection as ProductCollection;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\Framework\Api\SearchResultsInterfaceFactory;
use Magento\Framework\DataObject;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\Product;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable\Attribute;

/**
 * Fetch configurable product children data and add to final result of product collection.
 */
class ProductPlugin
{
    /**
     * @var Configurable
     */
    private $configurable;

    /**
     * @var AttributeCollection
     */
    private $attributeCollection;

    /**
     * @var ProductCollection
     */
    private $productCollection;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * ProductPlugin constructor.
     *
     * @param Configurable $configurable
     * @param AttributeCollection $attributeCollection
     * @param ProductCollection $productCollection
     * @param ProductRepositoryInterface $productRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param MetadataPool $metadataPool
     */
    public function __construct(
        Configurable $configurable,
        AttributeCollection $attributeCollection,
        ProductCollection $productCollection,
        ProductRepositoryInterface $productRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        MetadataPool $metadataPool
    ) {
        $this->configurable = $configurable;
        $this->attributeCollection = $attributeCollection;
        $this->productCollection = $productCollection;
        $this->productRepository = $productRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->metadataPool = $metadataPool;
    }

    /**
     * Intercept GraphQLCatalog getList, and add any necessary configurable fields
     *
     * @param Product $subject
     * @param SearchResultsInterface $result
     * @return SearchResultsInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetList(Product $subject, SearchResultsInterface $result)
    {
        $processConfigurableData = false;
        /** @var ProductInterface $product */
        foreach ($result->getItems() as $product) {
            if ($product->getTypeId() === Configurable::TYPE_CODE) {
                $this->productCollection->setProductFilter($product);
                $this->attributeCollection->setProductFilter($product);
                $processConfigurableData = true;
            }
        }

        if ($processConfigurableData) {
            /** @var \Magento\Catalog\Model\Product[] $children */
            $children = $this->productCollection->getItems();
            /** @var Attribute[] $attributes */
            $attributes = $this->attributeCollection->getItems();
            $result = $this->addConfigurableData($result, $children, $attributes);
        }

        return $result;
    }

    /**
     * Add configurable data to any configurable products in result set
     *
     * @param SearchResultsInterface $result
     * @param DataObject[] $children
     * @param DataObject[] $attributes
     * @return SearchResultsInterface
     */
    private function addConfigurableData($result, $children, $attributes)
    {
        $linkField = $this->metadataPool->getMetadata(ProductInterface::class)->getLinkField();
        foreach ($result->getItems() as $product) {
            if ($product->getTypeId() === Configurable::TYPE_CODE) {
                $extensionAttributes = $product->getExtensionAttributes();
                $childrenIds = [];
                foreach ($children as $child) {
                    if ($child->getParentId() === $product->getData($linkField)) {
                        $childrenIds[] = $child->getId();
                    }
                }
                $productAttributes = [];
                foreach ($attributes as $attribute) {
                    if ($attribute->getProductId() === $product->getId()) {
                        $productAttributes[] = $attribute;
                    }
                }
                $extensionAttributes->setConfigurableProductLinks($childrenIds);
                $extensionAttributes->setConfigurableProductOptions($productAttributes);
                $product->setExtensionAttributes($extensionAttributes);
            }
        }

        return $result;
    }
}
