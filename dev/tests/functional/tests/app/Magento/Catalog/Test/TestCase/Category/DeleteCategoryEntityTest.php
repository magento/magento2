<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\TestCase\Category;

use Magento\Catalog\Test\Fixture\Category;
use Magento\Catalog\Test\Page\Adminhtml\CatalogCategoryEdit;
use Magento\Catalog\Test\Page\Adminhtml\CatalogCategoryIndex;
use Magento\Mtf\TestCase\Injectable;

/**
 * Test Creation for DeleteCategoryEntity
 *
 * Test Flow:
 * Preconditions:
 * 1. Create category
 *
 * Steps:
 * 1. Log in to backend as admin user.
 * 2. Navigate PRODUCTS->Categories.
 * 3. Open category.
 * 4. Click "Delete" button.
 * 5. Perform asserts.
 *
 * @group Category_Management
 * @ZephyrId MAGETWO-23303
 */
class DeleteCategoryEntityTest extends Injectable
{
    /* tags */
    const MVP = 'yes';
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
     * Inject pages
     *
     * @param CatalogCategoryIndex $catalogCategoryIndex
     * @param CatalogCategoryEdit $catalogCategoryEdit
     * @return void
     */
    public function __inject(CatalogCategoryIndex $catalogCategoryIndex, CatalogCategoryEdit $catalogCategoryEdit)
    {
        $this->catalogCategoryIndex = $catalogCategoryIndex;
        $this->catalogCategoryEdit = $catalogCategoryEdit;
    }

    /**
     * Delete category.
     *
     * @param Category $category
     * @return void
     */
    public function test(Category $category)
    {
        $category->persist();
        $this->catalogCategoryIndex->open();
        $this->catalogCategoryIndex->getTreeCategories()->selectCategory($category);
        if ($this->catalogCategoryEdit->getFormPageActions()->checkDeleteButton()) {
            $this->catalogCategoryEdit->getFormPageActions()->delete();
            $this->catalogCategoryEdit->getModalBlock()->acceptAlert();
        }
    }
}
