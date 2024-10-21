<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Catalog\Plugin\Block;

use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\MenuCategoryData;
use Magento\Catalog\Model\ResourceModel\Category\StateDependentCollectionFactory;
use Magento\Framework\Data\Collection;
use Magento\Framework\Data\Tree\Node;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Plugin that enhances the top menu block by building and managing the category tree
 * for menu rendering in a storefront.
 */
class Topmenu
{
    /**
     * @param StateDependentCollectionFactory $collectionFactory
     * @param MenuCategoryData $menuCategoryData
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        private readonly StateDependentCollectionFactory $collectionFactory,
        private readonly MenuCategoryData $menuCategoryData,
        private readonly StoreManagerInterface $storeManager,
    ) {
    }

    /**
     * Build category tree for menu block.
     *
     * @param \Magento\Theme\Block\Html\Topmenu $subject
     * @param string $outermostClass
     * @param string $childrenWrapClass
     * @param int $limit
     *
     * @return void
     *
     * @SuppressWarnings("PMD.UnusedFormalParameter")
     */
    public function beforeGetHtml(
        \Magento\Theme\Block\Html\Topmenu $subject,
        string $outermostClass = '',
        string $childrenWrapClass = '',
        int $limit = 0
    ): void {
        $rootId = $this->storeManager->getStore()->getRootCategoryId();
        $storeId = $this->storeManager->getStore()->getId();
        /** @var \Magento\Catalog\Model\ResourceModel\Category\Collection $collection */
        $collection = $this->getCategoryTree($storeId, $rootId);
        $mapping = [$rootId => $subject->getMenu()];  // use nodes stack to avoid recursion
        foreach ($collection as $category) {
            $categoryParentId = $category->getParentId();
            if (!isset($mapping[$categoryParentId])) {
                $parentIds = $category->getParentIds();
                foreach ($parentIds as $parentId) {
                    if (isset($mapping[$parentId])) {
                        $categoryParentId = $parentId;
                    }
                }
            }

            /** @var Node $parentCategoryNode */
            $parentCategoryNode = $mapping[$categoryParentId];

            $categoryNode = new Node(
                $this->getCategoryAsArray(
                    $category,
                    $category->getParentId() == $categoryParentId
                ),
                'id',
                $parentCategoryNode->getTree(),
                $parentCategoryNode
            );
            $parentCategoryNode->addChild($categoryNode);

            $mapping[$category->getId()] = $categoryNode; //add node in stack
        }
    }

    /**
     * Add list of associated identities to the top menu block for caching purposes.
     *
     * @param \Magento\Theme\Block\Html\Topmenu $subject
     *
     * @return void
     */
    public function beforeGetIdentities(\Magento\Theme\Block\Html\Topmenu $subject): void
    {
        $subject->addIdentity(Category::CACHE_TAG);
        $rootId = $this->storeManager->getStore()->getRootCategoryId();
        $storeId = $this->storeManager->getStore()->getId();
        /** @var \Magento\Catalog\Model\ResourceModel\Category\Collection $collection */
        $collection = $this->getCategoryTree($storeId, $rootId);
        $mapping = [$rootId => $subject->getMenu()];  // use nodes stack to avoid recursion
        foreach ($collection as $category) {
            if (!isset($mapping[$category->getParentId()])) {
                continue;
            }

            $subject->addIdentity(Category::CACHE_TAG . '_' . $category->getId());
        }
    }

    /**
     * Convert category to array
     *
     * @param Category $category
     * @param bool $isParentActive
     *
     * @return array
     */
    private function getCategoryAsArray(Category $category, bool $isParentActive): array
    {
        $menuData = $this->menuCategoryData->getMenuCategoryData($category);
        $localData = [
            'is_category' => true,
            'is_parent_active' => $isParentActive
        ];
        return array_merge($localData, $menuData);
    }

    /**
     * Get Category Tree
     *
     * @param int $storeId
     * @param int $rootId
     *
     * @return \Magento\Catalog\Model\ResourceModel\Category\Collection
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getCategoryTree(int $storeId, int $rootId)
    {
        /** @var \Magento\Catalog\Model\ResourceModel\Category\Collection $collection */
        $collection = $this->collectionFactory->create();
        $collection->setStoreId($storeId);
        $collection->addAttributeToSelect('name');
        $collection->addFieldToFilter('path', ['like' => '1/' . $rootId . '/%']); //load only from store root
        $collection->addAttributeToFilter('include_in_menu', 1);
        $collection->addIsActiveFilter();
        $collection->addNavigationMaxDepthFilter();
        $collection->addUrlRewriteToResult();
        $collection->addOrder('level', Collection::SORT_ORDER_ASC);
        $collection->addOrder('position', Collection::SORT_ORDER_ASC);
        $collection->addOrder('parent_id', Collection::SORT_ORDER_ASC);
        $collection->addOrder('entity_id', Collection::SORT_ORDER_ASC);

        return $collection;
    }
}
