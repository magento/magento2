<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider;

use GraphQL\Language\AST\FieldNode;
use Magento\CatalogGraphQl\Model\Category\DepthCalculator;
use Magento\CatalogGraphQl\Model\Category\LevelCalculator;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Model\ResourceModel\Category\Collection;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\CatalogGraphQl\Model\AttributesJoiner;
use Magento\Catalog\Model\Category;

/**
 * Category tree data provider
 */
class CategoryTree
{
    /**
     * In depth we need to calculate only children nodes, so the first wrapped node should be ignored
     */
    const DEPTH_OFFSET = 1;

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
     * @var LevelCalculator
     */
    private $levelCalculator;

    /**
     * @var MetadataPool
     */
    private $metadata;

    /**
     * @param CollectionFactory $collectionFactory
     * @param AttributesJoiner $attributesJoiner
     * @param DepthCalculator $depthCalculator
     * @param LevelCalculator $levelCalculator
     * @param MetadataPool $metadata
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        AttributesJoiner $attributesJoiner,
        DepthCalculator $depthCalculator,
        LevelCalculator $levelCalculator,
        MetadataPool $metadata
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->attributesJoiner = $attributesJoiner;
        $this->depthCalculator = $depthCalculator;
        $this->levelCalculator = $levelCalculator;
        $this->metadata = $metadata;
    }

    /**
     * Returns categories tree starting from parent $rootCategoryId
     *
     * @param ResolveInfo $resolveInfo
     * @param int $rootCategoryId
     * @return \Iterator
     */
    public function getTree(ResolveInfo $resolveInfo, int $rootCategoryId): \Iterator
    {
        $categoryQuery = $resolveInfo->fieldNodes[0];
        $collection = $this->collectionFactory->create();
        $this->joinAttributesRecursively($collection, $categoryQuery);
        $depth = $this->depthCalculator->calculate($categoryQuery);
        $level = $this->levelCalculator->calculate($rootCategoryId);

        // If root category is being filter, we've to remove first slash
        if ($rootCategoryId == Category::TREE_ROOT_ID) {
            $regExpPathFilter = sprintf('.*%s/[/0-9]*$', $rootCategoryId);
        } else {
            $regExpPathFilter = sprintf('.*/%s/[/0-9]*$', $rootCategoryId);
        }

        //Search for desired part of category tree
        $collection->addPathFilter($regExpPathFilter);

        $collection->addFieldToFilter('level', ['gt' => $level]);
        $collection->addFieldToFilter('level', ['lteq' => $level + $depth - self::DEPTH_OFFSET]);
        $collection->setOrder('level');
        $collection->getSelect()->orWhere(
            $this->metadata->getMetadata(CategoryInterface::class)->getIdentifierField() . ' = ?',
            $rootCategoryId
        );
        return $collection->getIterator();
    }

    /**
     * Join attributes recursively
     *
     * @param Collection $collection
     * @param FieldNode $fieldNode
     * @return void
     */
    private function joinAttributesRecursively(Collection $collection, FieldNode $fieldNode) : void
    {
        if (!isset($fieldNode->selectionSet->selections)) {
            return;
        }

        $subSelection = $fieldNode->selectionSet->selections;
        $this->attributesJoiner->join($fieldNode, $collection);

        /** @var FieldNode $node */
        foreach ($subSelection as $node) {
            if ($node->kind === 'InlineFragment') {
                continue;
            }

            $this->joinAttributesRecursively($collection, $node);
        }
    }
}
