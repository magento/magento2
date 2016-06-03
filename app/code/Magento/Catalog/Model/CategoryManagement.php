<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model;

use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;

class CategoryManagement implements \Magento\Catalog\Api\CategoryManagementInterface
{
    /**
     * @var CategoryRepository
     */
    protected $categoryRepository;

    /**
     * @var \Magento\Catalog\Model\Category\Tree
     */
    protected $categoryTree;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;


    /**
     * @param \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository
     * @param Category\Tree $categoryTree
     * @param CollectionFactory $categoriesFactory
     */
    public function __construct(
        \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository,
        \Magento\Catalog\Model\Category\Tree $categoryTree,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoriesFactory
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->categoryTree = $categoryTree;
        $this->categoriesFactory = $categoriesFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getTree($rootCategoryId = null, $depth = null)
    {
        $category = null;
        if ($rootCategoryId !== null) {
            /** @var \Magento\Catalog\Model\Category $category */
            $category = $this->categoryRepository->get($rootCategoryId);
        } elseif ($this->isAdminStore()) {
            $category = $this->getTopLevelCategory();
        }
        $result = $this->categoryTree->getTree($this->categoryTree->getRootNode($category), $depth);
        return $result;
    }

    /**
     * Check is request use default scope
     *
     * @return bool
     */
    private function isAdminStore()
    {
        return $this->getStoreManager()->getStore()->getCode() == \Magento\Store\Model\Store::ADMIN_CODE;
    }

    /**
     * Get store manager for operations with admin code
     *
     * @return \Magento\Store\Model\StoreManagerInterface
     */
    private function getStoreManager()
    {
        if ($this->storeManager == null) {
            $this->storeManager = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Magento\Store\Model\StoreManagerInterface::class);
        }

        return $this->storeManager;
    }

    /**
     * Get top level hidden root category
     *
     * @return \Magento\Catalog\Model\Category
     */
    private function getTopLevelCategory()
    {
        $categoriesCollection = $this->categoriesFactory->create();
        return $categoriesCollection->addFilter('level', ['eq' => 0])->getFirstItem();
    }

    /**
     * {@inheritdoc}
     */
    public function move($categoryId, $parentId, $afterId = null)
    {
        $model = $this->categoryRepository->get($categoryId);
        $parentCategory = $this->categoryRepository->get($parentId);

        if ($parentCategory->hasChildren()) {
            $parentChildren = $parentCategory->getChildren();
            $categoryIds = explode(',', $parentChildren);
            $lastId = array_pop($categoryIds);
            $afterId = ($afterId === null || $afterId > $lastId) ? $lastId : $afterId;
        }

        if (strpos($parentCategory->getPath(), $model->getPath()) === 0) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Operation do not allow to move a parent category to any of children category')
            );
        }
        try {
            $model->move($parentId, $afterId);
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Could not move category'));
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getCount()
    {
        $categories = $this->categoriesFactory->create();
        /** @var \Magento\Catalog\Model\ResourceModel\Category\Collection $categories */
        $categories->addAttributeToFilter('parent_id', ['gt' => 0]);
        return $categories->getSize();
    }
}
