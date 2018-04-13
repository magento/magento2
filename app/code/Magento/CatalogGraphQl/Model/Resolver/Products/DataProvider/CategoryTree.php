<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider;

use GraphQL\Language\AST\FieldNode;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Model\ResourceModel\Category;
use Magento\Catalog\Model\ResourceModel\Category\Collection;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\CatalogGraphQl\Model\AttributesJoiner;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Reflection\DataObjectProcessor;

/**
 * Category tree data provider
 */
class CategoryTree
{
    /**
     * In depth we need to calculate only children nodes, so 2 first wrapped nodes should be ignored
     */
    const DEPTH_OFFSET = 2;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var AttributesJoiner
     */
    private $attributesJoiner;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var Category
     */
    private $resourceCategory;

    /**
     * @var CustomAttributesFlattener
     */
    private $customAttributesFlattener;

    /**
     * @var DataObjectProcessor
     */
    private $dataObjectProcessor;

    /**
     * @param CollectionFactory $collectionFactory
     * @param AttributesJoiner $attributesJoiner
     * @param ResourceConnection $resourceConnection
     * @param Category $resourceCategory
     * @param CustomAttributesFlattener $customAttributesFlattener
     * @param DataObjectProcessor $dataObjectProcessor
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        AttributesJoiner $attributesJoiner,
        ResourceConnection $resourceConnection,
        Category $resourceCategory,
        CustomAttributesFlattener $customAttributesFlattener,
        DataObjectProcessor $dataObjectProcessor
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->attributesJoiner = $attributesJoiner;
        $this->resourceConnection = $resourceConnection;
        $this->resourceCategory = $resourceCategory;
        $this->customAttributesFlattener = $customAttributesFlattener;
        $this->dataObjectProcessor = $dataObjectProcessor;
    }

    /**
     * @param ResolveInfo $resolveInfo
     * @param int $rootCategoryId
     * @return array
     */
    public function getTree(ResolveInfo $resolveInfo, int $rootCategoryId) : array
    {
        $categoryQuery = $resolveInfo->fieldASTs[0];
        $collection = $this->collectionFactory->create();
        $this->joinAttributesRecursively($collection, $categoryQuery);
        $depth = $this->calculateDepth($categoryQuery);
        $level = $this->getLevelByRootCategoryId($rootCategoryId);
        //Search for desired part of category tree
        $collection->addFieldToFilter('level', ['gt' => $level]);
        $collection->addFieldToFilter('level', ['lteq' => $level + $depth - self::DEPTH_OFFSET]);
        $collection->setOrder('level');
        $collection->getSelect()->orWhere($this->resourceCategory->getLinkField() . ' = ?', $rootCategoryId);
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
            $tree[$category->getId()] = $this->hydrateCategory($category);
            if ($nextCategory && (int) $nextCategory->getLevel() !== (int) $category->getLevel()) {
                $tree[$category->getId()]['children'] = $this->processTree($iterator);
            }
        }

        return $tree;
    }

    /**
     * Hydrate and flatten category object to flat array
     *
     * @param CategoryInterface $category
     * @return array
     */
    private function hydrateCategory(CategoryInterface $category) : array
    {
        $categoryData = $this->dataObjectProcessor->buildOutputDataArray($category, CategoryInterface::class);
        $categoryData['id'] = $category->getId();
        $categoryData['product_count'] = $category->getProductCount();
        $categoryData['all_children'] = $category->getAllChildren();
        $categoryData['children'] = [];
        $categoryData['available_sort_by'] = $category->getAvailableSortBy();
        return $this->customAttributesFlattener->flaternize($categoryData);
    }

    /**
     * @param int $rootCategoryId
     * @return int
     */
    private function getLevelByRootCategoryId(int $rootCategoryId) : int
    {
        $connection = $this->resourceConnection->getConnection();
        $select = $connection->select()
            ->from($connection->getTableName('catalog_category_entity'), 'level')
            ->where($this->resourceCategory->getLinkField() . " = ?", $rootCategoryId);
        return (int) $connection->fetchOne($select);
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
            $this->joinAttributesRecursively($collection, $node);
        }
    }

    /**
     * @param FieldNode $fieldNode
     * @return int
     */
    private function calculateDepth(FieldNode $fieldNode) : int
    {
        $selections = $fieldNode->selectionSet->selections ?? [];
        $depth = count($selections) ? 1 : 0;
        $childrenDepth = [0];
        foreach ($selections as $node) {
            $childrenDepth[] = $this->calculateDepth($node);
        }

        return $depth + max($childrenDepth);
    }
}
