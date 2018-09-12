<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider;

use GraphQL\Language\AST\FieldNode;
use Magento\CatalogGraphQl\Model\Category\DepthCalculator;
use Magento\CatalogGraphQl\Model\Category\Hydrator;
use Magento\CatalogGraphQl\Model\Category\LevelCalculator;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Model\ResourceModel\Category\Collection;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\CatalogGraphQl\Model\AttributesJoiner;

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
     * @var Hydrator
     */
    private $hydrator;

    /**
     * @param CollectionFactory $collectionFactory
     * @param AttributesJoiner $attributesJoiner
     * @param DepthCalculator $depthCalculator
     * @param LevelCalculator $levelCalculator
     * @param MetadataPool $metadata
     * @param Hydrator $hydrator
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        AttributesJoiner $attributesJoiner,
        DepthCalculator $depthCalculator,
        LevelCalculator $levelCalculator,
        MetadataPool $metadata,
        Hydrator $hydrator
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->attributesJoiner = $attributesJoiner;
        $this->depthCalculator = $depthCalculator;
        $this->levelCalculator = $levelCalculator;
        $this->metadata = $metadata;
        $this->hydrator = $hydrator;
    }

    /**
     * @param ResolveInfo $resolveInfo
     * @param int $rootCategoryId
     * @return array
     */
    public function getTree(ResolveInfo $resolveInfo, int $rootCategoryId) : array
    {
        $categoryQuery = $resolveInfo->fieldNodes[0];
        $collection = $this->collectionFactory->create();
        $this->joinAttributesRecursively($collection, $categoryQuery);
        $depth = $this->depthCalculator->calculate($categoryQuery);
        $level = $this->levelCalculator->calculate($rootCategoryId);
        //Search for desired part of category tree
        $collection->addPathFilter(sprintf('.*/%s/[/0-9]*$', $rootCategoryId));
        $collection->addFieldToFilter('level', ['gt' => $level]);
        $collection->addFieldToFilter('level', ['lteq' => $level + $depth - self::DEPTH_OFFSET]);
        $collection->setOrder('level');
        $collection->getSelect()->orWhere(
            $this->metadata->getMetadata(CategoryInterface::class)->getIdentifierField() . ' = ?',
            $rootCategoryId
        );
        return $this->processTree($collection->getIterator());
    }

    /**
     * @param \Iterator $iterator
     * @return array
     */
    private function processTree(\Iterator $iterator) : array
    {
        $tree = [];
        while ($iterator->valid()) {
            /** @var CategoryInterface $category */
            $category = $iterator->current();
            $iterator->next();
            $nextCategory = $iterator->current();
            $tree[$category->getId()] = $this->hydrator->hydrateCategory($category);
            if ($nextCategory && (int) $nextCategory->getLevel() !== (int) $category->getLevel()) {
                $tree[$category->getId()]['children'] = $this->processTree($iterator);
            }
        }

        return $tree;
    }

    /**
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
