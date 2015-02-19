<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Category;

use Magento\Framework\Data\Tree\Node;

/**
 * Retrieve category data represented in tree structure
 */
class Tree
{
    /**
     * @var \Magento\Catalog\Model\Resource\Category\Tree
     */
    protected $categoryTree;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Catalog\Model\Resource\Category\Collection
     */
    protected $categoryCollection;

    /**
     * @var \Magento\Catalog\Api\Data\CategoryTreeDataBuilder
     */
    protected $treeBuilder;

    /**
     * @param \Magento\Catalog\Model\Resource\Category\Tree $categoryTree
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Model\Resource\Category\Collection $categoryCollection
     * @param \Magento\Catalog\Api\Data\CategoryTreeDataBuilder $treeBuilder
     */
    public function __construct(
        \Magento\Catalog\Model\Resource\Category\Tree $categoryTree,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Resource\Category\Collection $categoryCollection,
        \Magento\Catalog\Api\Data\CategoryTreeDataBuilder $treeBuilder
    ) {
        $this->categoryTree = $categoryTree;
        $this->storeManager = $storeManager;
        $this->categoryCollection = $categoryCollection;
        $this->treeBuilder = $treeBuilder;
    }

    /**
     * @param \Magento\Catalog\Model\Category|null $category
     * @return Node|null
     */
    public function getRootNode($category = null)
    {
        if (!is_null($category) && $category->getId()) {
            return $this->getNode($category);
        }

        $store = $this->storeManager->getStore();
        $rootId = $store->getRootCategoryId();

        $tree = $this->categoryTree->load(null);
        $this->prepareCollection();
        $tree->addCollectionData($this->categoryCollection);
        $root = $tree->getNodeById($rootId);
        return $root;
    }

    /**
     * @param \Magento\Catalog\Model\Category $category
     * @return Node
     */
    protected function getNode(\Magento\Catalog\Model\Category $category)
    {
        $nodeId = $category->getId();
        $node = $this->categoryTree->loadNode($nodeId);
        $node->loadChildren();
        $this->prepareCollection();
        $this->categoryTree->addCollectionData($this->categoryCollection);
        return $node;
    }

    /**
     * @return void
     */
    protected function prepareCollection()
    {
        $storeId = $this->storeManager->getStore()->getId();
        $this->categoryCollection->addAttributeToSelect(
            'name'
        )->addAttributeToSelect(
            'is_active'
        )->setProductStoreId(
            $storeId
        )->setLoadProductCount(
            true
        )->setStoreId(
            $storeId
        );
    }

    /**
     * @param \Magento\Framework\Data\Tree\Node $node
     * @param int $depth
     * @param int $currentLevel
     * @return \Magento\Catalog\Api\Data\CategoryTreeInterface[]
     */
    public function getTree($node, $depth = null, $currentLevel = 0)
    {
        /** @var \Magento\Catalog\Api\Data\CategoryTreeInterface[] $children */
        $children = $this->getChildren($node, $depth, $currentLevel);
        $this->treeBuilder->setId($node->getId())
            ->setParentId($node->getParentId())
            ->setName($node->getName())
            ->setPosition($node->getPosition())
            ->setLevel($node->getLevel())
            ->setIsActive($node->getIsActive())
            ->setProductCount($node->getProductCount())
        ->setChildrenData($children);
        return $this->treeBuilder->create();
    }

    /**
     * @param \Magento\Framework\Data\Tree\Node $node
     * @param int $depth
     * @param int $currentLevel
     * @return \Magento\Catalog\Api\Data\CategoryTreeInterface[]|[]
     */
    protected function getChildren($node, $depth, $currentLevel)
    {
        if ($node->hasChildren()) {
            $children = [];
            foreach ($node->getChildren() as $child) {
                if (!is_null($depth) && $depth <= $currentLevel) {
                    break;
                }
                $children[] = $this->getTree($child, $depth, $currentLevel + 1);
            }
            return $children;
        }
        return [];
    }
}
