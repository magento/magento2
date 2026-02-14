<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Catalog\Model\Layer\Filter\DataProvider;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\Category as CategoryModel;
use Magento\Catalog\Model\CategoryFactory as CategoryModelFactory;
use Magento\Catalog\Model\Layer;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;

class Category
{
    /**
     * @var Registry
     */
    private $coreRegistry;

    /**
     * @var CategoryModel
     */
    private $category;

    /**
     * @var int
     */
    private $categoryId;

    /**
     * @var bool
     */
    private $isApplied = false;

    /**
     * @var Layer
     */
    private $layer;

    /**
     * @var CategoryModelFactory
     * @deprecated The category is loaded through the repository, which reuses already loaded instances.
     * @see CategoryRepositoryInterface::get()
     */
    private $categoryFactory;

    /**
     * @var CategoryRepositoryInterface
     */
    private $categoryRepository;

    /**
     * @param Registry $coreRegistry
     * @param CategoryModelFactory $categoryFactory
     * @param Layer $layer
     * @param CategoryRepositoryInterface|null $categoryRepository
     */
    public function __construct(
        Registry $coreRegistry,
        CategoryModelFactory $categoryFactory,
        Layer $layer,
        ?CategoryRepositoryInterface $categoryRepository = null
    ) {
        $this->coreRegistry = $coreRegistry;
        $this->layer = $layer;
        $this->categoryFactory = $categoryFactory;
        $this->categoryRepository = $categoryRepository
            ?? ObjectManager::getInstance()->get(CategoryRepositoryInterface::class);
    }

    /**
     * Validate category for using as filter
     *
     * @return mixed
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
     * Set the id of the category to use as filter
     *
     * @param int $categoryId
     * @return $this
     */
    public function setCategoryId($categoryId)
    {
        $this->isApplied = true;
        $this->category = null;
        $this->categoryId = $categoryId;

        return $this;
    }

    /**
     * Check whether a category id was set
     *
     * @return boolean
     */
    private function isApplied()
    {
        return $this->isApplied;
    }

    /**
     * Get selected category object
     *
     * @return CategoryModel
     */
    public function getCategory()
    {
        if ($this->category === null) {
            $category = null;
            if ($this->categoryId !== null) {
                $category = $this->loadCategory();
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
     * Load the category for the current store
     *
     * The repository keeps loaded categories in memory, so a category already loaded during the request
     *
     * @return CategoryModel|null
     */
    private function loadCategory()
    {
        if (!is_numeric($this->categoryId)) {
            return null;
        }

        try {
            return $this->categoryRepository->get(
                (int) $this->categoryId,
                $this->getLayer()->getCurrentStore()->getId()
            );
        } catch (NoSuchEntityException $e) {
            return null;
        }
    }

    /**
     * Get filter value for reset current filter state
     *
     * @return mixed|null
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
     */
    private function getLayer()
    {
        return $this->layer;
    }
}
