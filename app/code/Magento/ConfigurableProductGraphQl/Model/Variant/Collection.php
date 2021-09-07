<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProductGraphQl\Model\Variant;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable\Product\Collection as ChildCollection;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable\Product\CollectionFactory;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\GraphQl\Model\Query\ContextInterface;
use Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\Product\CollectionProcessorInterface;
use Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\Product\CollectionPostProcessor;
use Magento\Catalog\Model\Product\Attribute\Source\Status;

/**
 * Collection for fetching configurable child product data.
 */
class Collection
{
    /**
     * @var CollectionFactory
     */
    private $childCollectionFactory;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @var Product[]
     */
    private $parentProducts = [];

    /**
     * @var array
     */
    private $childrenMap = [];

    /**
     * @var string[]
     */
    private $attributeCodes = [];

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @var CollectionPostProcessor
     */
    private $collectionPostProcessor;

    /**
     * @param CollectionFactory $childCollectionFactory
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param MetadataPool $metadataPool
     * @param CollectionProcessorInterface $collectionProcessor
     * @param CollectionPostProcessor $collectionPostProcessor
     */
    public function __construct(
        CollectionFactory $childCollectionFactory,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        MetadataPool $metadataPool,
        CollectionProcessorInterface $collectionProcessor,
        CollectionPostProcessor $collectionPostProcessor
    ) {
        $this->childCollectionFactory = $childCollectionFactory;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->metadataPool = $metadataPool;
        $this->collectionProcessor = $collectionProcessor;
        $this->collectionPostProcessor = $collectionPostProcessor;
    }

    /**
     * Add parent to collection filter
     *
     * @param Product $product
     * @return void
     */
    public function addParentProduct(Product $product) : void
    {
        $linkField = $this->metadataPool->getMetadata(ProductInterface::class)->getLinkField();
        $productId = $product->getData($linkField);

        if (isset($this->parentProducts[$productId])) {
            return;
        }

        if (!empty($this->childrenMap)) {
            $this->childrenMap = [];
        }
        $this->parentProducts[$productId] = $product;
    }

    /**
     * Add attributes to collection filter
     *
     * @param array $attributeCodes
     * @return void
     */
    public function addEavAttributes(array $attributeCodes) : void
    {
        $this->attributeCodes = array_replace($this->attributeCodes, $attributeCodes);
    }

    /**
     * Retrieve child products from for passed in parent id.
     *
     * @param int $id
     * @param ContextInterface $context
     * @return array
     */
    public function getChildProductsByParentId(int $id, ContextInterface $context) : array
    {
        $childrenMap = $this->fetch($context);

        if (!isset($childrenMap[$id])) {
            return [];
        }

        return $childrenMap[$id];
    }

    /**
     * Fetch all children products from parent id's.
     *
     * @param ContextInterface $context
     * @return array
     */
    private function fetch(ContextInterface $context) : array
    {
        if (empty($this->parentProducts) || !empty($this->childrenMap)) {
            return $this->childrenMap;
        }

        foreach ($this->parentProducts as $product) {
            $attributeData = $this->getAttributesCodes($product);
            /** @var ChildCollection $childCollection */
            $childCollection = $this->childCollectionFactory->create();
            $childCollection->setProductFilter($product);
            $childCollection->addWebsiteFilter($context->getExtensionAttributes()->getStore()->getWebsiteId());
            $this->collectionProcessor->process(
                $childCollection,
                $this->searchCriteriaBuilder->create(),
                $attributeData,
                $context
            );
            $childCollection->load();
            $this->collectionPostProcessor->process($childCollection, $attributeData);

            /** @var Product $childProduct */
            foreach ($childCollection as $childProduct) {
                if ((int)$childProduct->getStatus() !== Status::STATUS_ENABLED) {
                    continue;
                }
                $formattedChild = ['model' => $childProduct, 'sku' => $childProduct->getSku()];
                $parentId = (int)$childProduct->getParentId();
                if (!isset($this->childrenMap[$parentId])) {
                    $this->childrenMap[$parentId] = [];
                }

                $this->childrenMap[$parentId][] = $formattedChild;
            }
        }

        return $this->childrenMap;
    }

    /**
     * Get attributes codes for given product
     *
     * @param Product $currentProduct
     * @return array
     */
    private function getAttributesCodes(Product $currentProduct): array
    {
        $attributeCodes = $this->attributeCodes;
        if ($currentProduct->getTypeId() == Configurable::TYPE_CODE) {
            $allowAttributes = $currentProduct->getTypeInstance()->getConfigurableAttributes($currentProduct);
            foreach ($allowAttributes as $attribute) {
                $productAttribute = $attribute->getProductAttribute();
                if (!\in_array($productAttribute->getAttributeCode(), $attributeCodes)) {
                    $attributeCodes[] = $productAttribute->getAttributeCode();
                }
            }
        }

        return $attributeCodes;
    }
}
