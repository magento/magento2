<?php
/**
 *
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
