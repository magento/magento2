<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProductGraphQl\Model\Variant;

use Exception;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable\Product\Collection as ChildCollection;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable\Product\CollectionFactory;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\ObjectManager\ResetAfterRequestInterface;
use Magento\GraphQl\Model\Query\ContextInterface;
use Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\Product\CollectionProcessorInterface;
use Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\Product\CollectionPostProcessor;
use Magento\Catalog\Model\Product\Attribute\Source\Status;

/**
 * Collection for fetching configurable child product data.
 */
class Collection implements ResetAfterRequestInterface
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
     * @throws Exception
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
     * @param array $attributeCodes
     * @return array
     */
    public function getChildProductsByParentId(int $id, ContextInterface $context, array $attributeCodes) : array
    {
        $childrenMap = $this->fetch($context, $attributeCodes);

        if (!isset($childrenMap[$id])) {
            return [];
        }

        return $childrenMap[$id];
    }

    /**
     * Fetch all children products from parent id's.
     *
     * @param ContextInterface $context
     * @param array $attributeCodes
     * @return array
     * @throws Exception
     */
    private function fetch(ContextInterface $context, array $attributeCodes) : array
    {
        if (empty($this->parentProducts) || !empty($this->childrenMap)) {
            return $this->childrenMap;
        }

        /** @var ChildCollection $childCollection */
        $childCollection = $this->childCollectionFactory->create();
        foreach ($this->parentProducts as $product) {
            $childCollection->setProductFilter($product);
        }
        $childCollection->addWebsiteFilter($context->getExtensionAttributes()->getStore()->getWebsiteId());
        $linkField = $this->metadataPool->getMetadata(ProductInterface::class)->getLinkField();
        $childCollection->getSelect()->group('e.' . $linkField);
        // PgCompat: GROUP_CONCAT() is MySQL-only - getGroupConcatSql() isn't part of
        // AdapterInterface, but this deployment's adapter implements it as a
        // string_agg() equivalent.
        //
        // ChildCollection::_initSelect() unconditionally selects raw link_table.parent_id
        // (needed by every OTHER caller of that base collection, which don't group at
        // all); once this method's own group('e.' . $linkField) is added on top, that raw
        // column is no longer functionally dependent on the GROUP BY key (a child can
        // have more than one parent) and Postgres rejects it outright - the whole reason
        // parent_ids exists is to carry that same information aggregated instead. Drop
        // the raw column here, narrowly, via setPart() rather than in the base class,
        // since only this grouped call site is affected.
        $select = $childCollection->getSelect();
        $select->setPart(
            \Magento\Framework\DB\Select::COLUMNS,
            array_values(array_filter(
                $select->getPart(\Magento\Framework\DB\Select::COLUMNS),
                static fn(array $column) => !($column[0] === 'link_table' && $column[1] === 'parent_id')
            ))
        );
        $select->columns([
            'parent_ids' => $select->getAdapter()->getGroupConcatSql('link_table.parent_id')
        ]);

        $attributeCodes = array_unique(array_merge($this->attributeCodes, $attributeCodes));

        $this->collectionProcessor->process(
            $childCollection,
            $this->searchCriteriaBuilder->create(),
            $attributeCodes,
            $context
        );
        $this->collectionPostProcessor->process($childCollection, $attributeCodes);

        /** @var Product $childProduct */
        foreach ($childCollection as $childProduct) {
            if ((int)$childProduct->getStatus() !== Status::STATUS_ENABLED) {
                continue;
            }
            $formattedChild = ['model' => $childProduct, 'sku' => $childProduct->getSku()];
            $parentIds = $childProduct->getParentIds() ? explode(',', $childProduct->getParentIds()) : [];
            foreach ($parentIds as $parentId) {
                if (!isset($this->childrenMap[$parentId])) {
                    $this->childrenMap[$parentId] = [];
                }
                $this->childrenMap[$parentId][] = $formattedChild;
            }
        }
        return $this->childrenMap;
    }

    /**
     * @inheritDoc
     */
    public function _resetState(): void
    {
        $this->parentProducts = [];
        $this->childrenMap = [];
        $this->attributeCodes = [];
    }
}
