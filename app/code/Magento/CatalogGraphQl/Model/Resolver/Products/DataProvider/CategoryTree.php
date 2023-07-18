<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider;

use GraphQL\Language\AST\FieldNode;
use GraphQL\Language\AST\NodeKind;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Model\ResourceModel\Category\Collection;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\CatalogGraphQl\Model\AttributesJoiner;
use Magento\CatalogGraphQl\Model\Category\DepthCalculator;
use Magento\CatalogGraphQl\Model\Resolver\Categories\DataProvider\Category\CollectionProcessorInterface;
use Magento\Framework\DB\Sql\Expression;
use Magento\Framework\Api\Search\SearchCriteria;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\GraphQl\Model\Query\ContextInterface;

/**
 * Category tree data provider
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CategoryTree
{
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var AttributesJoiner
     */
    private $attributesJoiner;

    /**
     * @var DepthCalculator
     */
    private $depthCalculator;

    /**
     * @var MetadataPool
     */
    private $metadata;

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @param CollectionFactory $collectionFactory
     * @param AttributesJoiner $attributesJoiner
     * @param DepthCalculator $depthCalculator
     * @param MetadataPool $metadata
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        AttributesJoiner $attributesJoiner,
        DepthCalculator $depthCalculator,
        MetadataPool $metadata,
        CollectionProcessorInterface $collectionProcessor
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->attributesJoiner = $attributesJoiner;
        $this->depthCalculator = $depthCalculator;
        $this->metadata = $metadata;
        $this->collectionProcessor = $collectionProcessor;
    }

    /**
     * Returns categories collection for tree starting from parent $rootCategoryId
     *
     * @param ResolveInfo $resolveInfo
     * @param int $rootCategoryId
     * @param int $storeId
     * @return Collection
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getTreeCollection(ResolveInfo $resolveInfo, int $rootCategoryId, int $storeId): Collection
    {
        return $this->getRawTreeCollection($resolveInfo, [$rootCategoryId]);
    }

    /**
     * Join attributes recursively
     *
     * @param Collection $collection
     * @param FieldNode $fieldNode
     * @param ResolveInfo $resolveInfo
     * @return void
     */
    private function joinAttributesRecursively(
        Collection $collection,
        FieldNode $fieldNode,
        ResolveInfo $resolveInfo
    ): void {
        if (!isset($fieldNode->selectionSet->selections)) {
            return;
        }

        $subSelection = $fieldNode->selectionSet->selections;
        $this->attributesJoiner->join($fieldNode, $collection, $resolveInfo);

        /** @var FieldNode $node */
        foreach ($subSelection as $node) {
            if ($node->kind === NodeKind::INLINE_FRAGMENT || $node->kind === NodeKind::FRAGMENT_SPREAD) {
                continue;
            }
            $this->joinAttributesRecursively($collection, $node, $resolveInfo);
        }
    }

    /**
     * Returns categories tree starting from parent $rootCategoryId with filtration
     *
     * @param ResolveInfo $resolveInfo
     * @param array $topLevelCategoryIds
     * @param SearchCriteria $searchCriteria
     * @param array $attributeNames
     * @param ContextInterface $context
     * @return Collection
     * @throws LocalizedException
     */
    public function getFlatCategoriesByRootIds(
        ResolveInfo $resolveInfo,
        array $topLevelCategoryIds,
        SearchCriteria $searchCriteria,
        array $attributeNames,
        ContextInterface $context
    ): Collection {
        $collection = $this->getRawTreeCollection($resolveInfo, $topLevelCategoryIds);
        $this->collectionProcessor->process($collection, $searchCriteria, $attributeNames, $context);
        return $collection;
    }

    /**
     * Return prepared collection
     *
     * @param ResolveInfo $resolveInfo
     * @param array $topLevelCategoryIds
     * @return Collection
     * @throws LocalizedException
     */
    private function getRawTreeCollection(ResolveInfo $resolveInfo, array $topLevelCategoryIds) : Collection
    {
        $categoryQuery = $resolveInfo->fieldNodes[0];
        $collection = $this->collectionFactory->create();
        $this->joinAttributesRecursively($collection, $categoryQuery, $resolveInfo);
        $depth = $this->depthCalculator->calculate($resolveInfo, $categoryQuery);
        $collection->getSelect()->distinct()->joinInner(
            ['base' => $collection->getTable('catalog_category_entity')],
            $collection->getConnection()->quoteInto('base.entity_id in (?)', $topLevelCategoryIds),
            ''
        );
        $collection->addFieldToFilter(
            'level',
            ['lteq' => new Expression(
                $collection->getConnection()->quoteInto('base.level + ?', $depth - 1)
            )]
        );
        $collection->addFieldToFilter(
            'path',
            [
                ['eq' => new Expression('base.path')],
                ['like' => new Expression('concat(base.path, \'/%\')')]
            ]
        );

        //Add `is_anchor` attribute to selected field
        $collection->addAttributeToSelect('is_anchor');
        $collection->addAttributeToFilter('is_active', 1, "left");
        $collection->setOrder('level');
        $collection->setOrder(
            'position',
            $collection::SORT_ORDER_DESC
        );
        $collection->getSelect()->orWhere(
            $collection->getSelect()
                ->getConnection()
                ->quoteIdentifier(
                    'e.' . $this->metadata->getMetadata(CategoryInterface::class)->getIdentifierField()
                ) . ' IN (?)',
            $topLevelCategoryIds
        );
        return $collection;
    }
}
