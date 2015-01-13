<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model;

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
     * @param \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository
     * @param Category\Tree $categoryTree
     */
    public function __construct(
        \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository,
        \Magento\Catalog\Model\Category\Tree $categoryTree
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->categoryTree = $categoryTree;
    }

    /**
     * {@inheritdoc}
     */
    public function getTree($rootCategoryId = null, $depth = null)
    {
        $category = null;
        if (!is_null($rootCategoryId)) {
            /** @var \Magento\Catalog\Model\Category $category */
            $category = $this->categoryRepository->get($rootCategoryId);
        }
        $result = $this->categoryTree->getTree($this->categoryTree->getRootNode($category), $depth);
        return $result;
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
            $afterId = (is_null($afterId) || $afterId > $lastId) ? $lastId : $afterId;
        }

        if (strpos($parentCategory->getPath(), $model->getPath()) === 0) {
            throw new \Magento\Framework\Model\Exception(
                "Operation do not allow to move a parent category to any of children category"
            );
        }
        try {
            $model->move($parentId, $afterId);
        } catch (\Exception $e) {
            throw new \Magento\Framework\Model\Exception('Could not move category');
        }
        return true;
    }
}
