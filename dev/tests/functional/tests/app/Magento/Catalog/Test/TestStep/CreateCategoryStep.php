<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\TestStep;

use Magento\Catalog\Test\Fixture\Category;
use Magento\Catalog\Test\Page\Adminhtml\CatalogCategoryEdit;
use Magento\Catalog\Test\Page\Adminhtml\CatalogCategoryIndex;
use Magento\Mtf\TestStep\TestStepInterface;

/**
 * Create category without assigned products step.
 */
class CreateCategoryStep implements TestStepInterface
{
    /**
     * Catalog category index page.
     *
     * @var CatalogCategoryIndex
     */
    private $catalogCategoryIndex;

    /**
     * Catalog category edit page.
     *
     * @var CatalogCategoryEdit
     */
    private $catalogCategoryEdit;

    /**
     * Category fixture.
     *
     * @var Category
     */
    private $category;

    /**
     * Type of added category.
     *
     * @var string
     */
    private $addCategory;

    /**
     * @param CatalogCategoryIndex $catalogCategoryIndex
     * @param CatalogCategoryEdit $catalogCategoryEdit
     * @param Category $category
     * @param string $addCategory
     * @return void
     */
    public function __construct(
        CatalogCategoryIndex $catalogCategoryIndex,
        CatalogCategoryEdit $catalogCategoryEdit,
        Category $category,
        $addCategory
    ) {
        $this->catalogCategoryIndex = $catalogCategoryIndex;
        $this->catalogCategoryEdit = $catalogCategoryEdit;
        $this->category = $category;
        $this->addCategory = $addCategory;
    }

    /**
     * Creates category without assigned products.
     *
     * @return array
     */
    public function run()
    {
        $this->catalogCategoryIndex->open();
        $this->catalogCategoryIndex->getTreeCategories()->selectCategory($this->category, false);
        $typeOfAddedCategory = $this->addCategory;
        $this->catalogCategoryIndex->getTreeCategories()->$typeOfAddedCategory();
        $this->catalogCategoryEdit->getEditForm()->fill($this->category);
        $this->catalogCategoryEdit->getFormPageActions()->save();

        return ['category' => $this->category];
    }
}
