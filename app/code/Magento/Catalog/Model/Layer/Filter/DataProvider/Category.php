<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Layer\Filter\DataProvider;

use Magento\Catalog\Model\Category as CategoryModel;
use Magento\Catalog\Model\CategoryFactory as CategoryModelFactory;
use Magento\Catalog\Model\Layer;
use Magento\Framework\Registry;

/**
 * Class \Magento\Catalog\Model\Layer\Filter\DataProvider\Category
 *
 * @since 2.0.0
 */
class Category
{
    /**
     * @var Registry
     * @since 2.0.0
     */
    private $coreRegistry;

    /**
     * @var CategoryModel
     * @since 2.0.0
     */
    private $category;

    /**
     * @var int
     * @since 2.0.0
     */
    private $categoryId;

    /**
     * @var bool
     * @since 2.0.0
     */
    private $isApplied = false;

    /**
     * @var Layer
     * @since 2.0.0
     */
    private $layer;

    /**
     * @var CategoryModelFactory
     * @since 2.0.0
     */
    private $categoryFactory;

    /**
     * @param Registry $coreRegistry
     * @param CategoryModelFactory $categoryFactory
     * @param Layer $layer
     * @internal param $data
     * @since 2.0.0
     */
    public function __construct(
        Registry $coreRegistry,
        CategoryModelFactory $categoryFactory,
        Layer $layer
    ) {
        $this->coreRegistry = $coreRegistry;
        $this->layer = $layer;
        $this->categoryFactory = $categoryFactory;
    }

    /**
     * Validate category for using as filter
     *
     * @return mixed
     * @since 2.0.0
     */
    public function isValid()
    {
        $category = $this->getCategory();
        if ($category->getId()) {
            while ($category->getLevel() != 0) {
                if (!$category->getIsActive()) {
                    return false;
                }
                $category = $category->getParentCategory();
            }

            return true;
        }

        return false;
    }

    /**
     * @param int $categoryId
     * @return $this
     * @since 2.0.0
     */
    public function setCategoryId($categoryId)
    {
        $this->isApplied = true;
        $this->category = null;
        $this->categoryId = $categoryId;

        return $this;
    }

    /**
     * @return boolean
     * @since 2.0.0
     */
    private function isApplied()
    {
        return $this->isApplied;
    }

    /**
     * Get selected category object
     *
     * @return CategoryModel
     * @since 2.0.0
     */
    public function getCategory()
    {
        if ($this->category === null) {
            /** @var CategoryModel|null $category */
            $category = null;
            if ($this->categoryId !== null) {
                $category = $this->categoryFactory->create()
                    ->setStoreId(
                        $this->getLayer()
                            ->getCurrentStore()
                            ->getId()
                    )
                    ->load($this->categoryId);
            }

            if ($category === null || !$category->getId()) {
                $category = $this->getLayer()
                    ->getCurrentCategory();
            }

            $this->coreRegistry->register('current_category_filter', $category, true);
            $this->category = $category;
        }

        return $this->category;
    }

    /**
     * Get filter value for reset current filter state
     *
     * @return mixed|null
     * @since 2.0.0
     */
    public function getResetValue()
    {
        if ($this->isApplied()) {
            /**
             * Revert path ids
             */
            $category = $this->getCategory();
            $pathIds = array_reverse($category->getPathIds());
            $curCategoryId = $this->getLayer()
                ->getCurrentCategory()
                ->getId();
            if (isset($pathIds[1]) && $pathIds[1] != $curCategoryId) {
                return $pathIds[1];
            }
        }

        return null;
    }

    /**
     * @return Layer
     * @since 2.0.0
     */
    private function getLayer()
    {
        return $this->layer;
    }
}
