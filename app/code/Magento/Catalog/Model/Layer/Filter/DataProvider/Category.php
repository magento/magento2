<?php
/**
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
namespace Magento\Catalog\Model\Layer\Filter\DataProvider;

use Magento\Catalog\Model\Category as CategoryModel;
use Magento\Catalog\Model\CategoryFactory as CategoryModelFactory;
use Magento\Catalog\Model\Layer;
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
     */
    private $categoryFactory;

    /**
     * @param Registry $coreRegistry
     * @param CategoryModelFactory $categoryFactory
     * @param Layer $layer
     * @internal param $data
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
        if (is_null($this->category)) {
            /** @var CategoryModel|null $category */
            $category = null;
            if (!is_null($this->categoryId)) {
                $category = $this->categoryFactory->create()
                    ->setStoreId(
                        $this->getLayer()
                            ->getCurrentStore()
                            ->getId()
                    )
                    ->load($this->categoryId);
            }

            if (is_null($category) || !$category->getId()) {
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
