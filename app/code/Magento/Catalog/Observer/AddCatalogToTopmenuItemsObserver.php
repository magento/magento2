<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Observer;

use Magento\Catalog\Model\Category;
use Magento\Framework\Data\Collection;
use Magento\Framework\Data\Tree\Node;
use Magento\Framework\Event\ObserverInterface;

class AddCatalogToTopmenuItemsObserver implements ObserverInterface
{
    /**
     * Catalog category
     *
     * @var \Magento\Catalog\Helper\Category
     */
    protected $catalogCategory;

    /**
     * @var \Magento\Catalog\Model\Indexer\Category\Flat\State
     */
    protected $categoryFlatState;

    /**
     * @var MenuCategoryData
     */
    protected $menuCategoryData;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param \Magento\Catalog\Helper\Category $catalogCategory
     * @param \Magento\Catalog\Model\Indexer\Category\Flat\State $categoryFlatState
     * @param \Magento\Catalog\Observer\MenuCategoryData $menuCategoryData
     */
    public function __construct(
        \Magento\Catalog\Helper\Category $catalogCategory,
        \Magento\Catalog\Model\Indexer\Category\Flat\State $categoryFlatState,
        \Magento\Catalog\Observer\MenuCategoryData $menuCategoryData,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $collectionFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->catalogCategory = $catalogCategory;
        $this->categoryFlatState = $categoryFlatState;
        $this->menuCategoryData = $menuCategoryData;
        $this->collectionFactory = $collectionFactory;
        $this->storeManager = $storeManager;
    }

    /**
     * Checking whether the using static urls in WYSIWYG allowed event
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $block = $observer->getEvent()->getBlock();
        $menuRootNode = $observer->getEvent()->getMenu();

        $block->addIdentity(Category::CACHE_TAG);

        $rootId = $this->storeManager->getStore()->getRootCategoryId();

        /** @var \Magento\Catalog\Model\ResourceModel\Category\Collection $collection */
        $collection = $this->collectionFactory->create()
            ->setStoreId($this->storeManager->getStore()->getId())
            ->addAttributeToSelect('name')
                ->addFieldToFilter('path', ['like' => '1/' . $rootId . '/%']) //load only from store root
            ->addAttributeToFilter('include_in_menu', 1) //
            ->addIsActiveFilter()
            ->addOrder('level', Collection::SORT_ORDER_ASC)
            ->addOrder('position', Collection::SORT_ORDER_ASC)
            ->addOrder('parent_id', Collection::SORT_ORDER_ASC)
            ->addOrder('entity_id', Collection::SORT_ORDER_ASC);


        $mapping = [$rootId => $menuRootNode];  // use nodes stack to avoid recursion
        foreach ($collection as $category) {
            /** @var Node $parentCategoryNode */
            $parentCategoryNode = $mapping[$category->getParentId()];
            $categoryNode = new Node(
                $this->menuCategoryData->getMenuCategoryData($category),
                'id',
                $parentCategoryNode->getTree(),
                $parentCategoryNode
            );
            $parentCategoryNode->addChild($categoryNode);

            $mapping[$category->getId()] = $categoryNode; //add node in stack

            $block->addIdentity(Category::CACHE_TAG . '_' . $category->getId());
        }
    }
}
