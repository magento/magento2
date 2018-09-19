<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogUrlRewrite\Test\TestCase;

use Magento\Catalog\Test\Fixture\Category;
use Magento\Catalog\Test\Page\Adminhtml\CatalogCategoryEdit;
use Magento\Catalog\Test\Page\Adminhtml\CatalogCategoryIndex;
use Magento\Mtf\TestCase\Injectable;

/**
 * Test Creation for CreateCategoryEntity
 *
 * Test Flow:
 * 1. Login as admin
 * 2. Navigate to the Products>Inventory>Categories
 * 3. Click on 'Add Category' button
 * 4. Fill out all data according to data set
 * 5. Save category
 * 6. Verify created category
 * 7. Navigate to the Products>Inventory>Categories
 * 8. Click on 'Add Category' button
 * 9. Fill out all data according to the same data set
 * 10. Save category
 * 11. Verify that a friendly url exists error message is displayed
 *
 * @group Category_Management
 * @ZephyrId MAGETWO-70307
 */
class CreateDuplicateUrlCategoryEntityTest extends Injectable
{
    /* tags */
    const MVP = 'yes';
    const TEST_TYPE = 'acceptance_test, extended_acceptance_test';
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
     * Create category
     *
     * @param Category $category
     * @param string $addCategory
     * @return array
     */
    public function test(Category $category, $addCategory)
    {
        for ($index = 0; $index < 2; $index++) {
            // Duplicate category
            $this->catalogCategoryIndex->open();
            $this->catalogCategoryIndex->getTreeCategories()->selectCategory($category, false);
            $this->catalogCategoryIndex->getTreeCategories()->$addCategory();
            $this->catalogCategoryEdit->getEditForm()->fill($category);
            $this->catalogCategoryEdit->getFormPageActions()->save();
        }

        return ['category' => $category];
    }
}
