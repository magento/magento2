<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\TestCase\Category;

use Magento\Catalog\Test\Fixture\Category;
use Magento\Catalog\Test\Page\Adminhtml\CatalogCategoryEdit;
use Magento\Catalog\Test\Page\Adminhtml\CatalogCategoryIndex;
use Magento\Mtf\TestCase\Injectable;

/**
 * Test Creation for UpdateCategoryEntity
 *
 * Test Flow:
 * Preconditions:
 * 1. Create category
 *
 * Steps:
 * 1. Login as admin
 * 2. Navigate Products->Categories
 * 3. Open category created in preconditions
 * 4. Update data according to data set
 * 5. Save
 * 6. Perform asserts
 *
 * @group Category_Management_(MX)
 * @ZephyrId MAGETWO-23290
 */
class UpdateCategoryEntityTest extends Injectable
{
    /* tags */
    const MVP = 'yes';
    const DOMAIN = 'MX';
    /* end tags */

    /**
     * Catalog category index page
     *
     * @var CatalogCategoryIndex
     */
    protected $catalogCategoryIndex;

    /**
     * Catalog category edit page
     *
     * @var CatalogCategoryEdit
     */
    protected $catalogCategoryEdit;

    /**
     * Inject page end prepare default category
     *
     * @param Category $initialCategory
     * @param CatalogCategoryIndex $catalogCategoryIndex
     * @param CatalogCategoryEdit $catalogCategoryEdit
     * @return array
     */
    public function __inject(
        Category $initialCategory,
        CatalogCategoryIndex $catalogCategoryIndex,
        CatalogCategoryEdit $catalogCategoryEdit
    ) {
        $this->catalogCategoryIndex = $catalogCategoryIndex;
        $this->catalogCategoryEdit = $catalogCategoryEdit;
        $initialCategory->persist();
        return ['initialCategory' => $initialCategory];
    }

    /**
     * Test for update category
     *
     * @param Category $category
     * @param Category $initialCategory
     * @return void
     */
    public function test(Category $category, Category $initialCategory)
    {
        $this->catalogCategoryIndex->open();
        $this->catalogCategoryIndex->getTreeCategories()->selectCategory($initialCategory);
        $this->catalogCategoryEdit->getEditForm()->fill($category);
        $this->catalogCategoryEdit->getFormPageActions()->save();
    }
}
