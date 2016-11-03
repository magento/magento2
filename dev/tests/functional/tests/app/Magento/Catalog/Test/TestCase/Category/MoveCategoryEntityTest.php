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
 * Test Creation for CreateCategoryEntity
 *
 * Test Flow:
 * 1. Login as admin
 * 2. Navigate to the Products>Inventory>Categories
 * 3. Click on 'Add Category' button
 * 4. Fill out all data according to data set
 * 5. Save category
 * 6. Verify created category
 *
 * @ZephyrId MAGETWO-27319
 */
class MoveCategoryEntityTest extends Injectable
{
    /**
     * CatalogCategoryIndex page.
     *
     * @var CatalogCategoryIndex
     */
    private $catalogCategoryIndex;

    /**
     * CatalogCategoryEdit page.
     *
     * @var CatalogCategoryEdit
     */
    private $catalogCategoryEdit;

    /**
     * Inject page end prepare default category.
     *
     * @param CatalogCategoryIndex $catalogCategoryIndex
     * @param CatalogCategoryEdit $catalogCategoryEdit
     * @return array
     */
    public function __inject(
        CatalogCategoryIndex $catalogCategoryIndex,
        CatalogCategoryEdit $catalogCategoryEdit
    ) {
        $this->catalogCategoryIndex = $catalogCategoryIndex;
        $this->catalogCategoryEdit = $catalogCategoryEdit;
    }

    /**
     * Runs test.
     *
     * @param Category $childCategory
     * @param Category $parentCategory
     * @return array
     */
    public function test(Category $childCategory, Category $parentCategory)
    {
        //Preconditions:
        $parentCategory->persist();
        $childCategory->persist();

        // Steps:
        $this->catalogCategoryIndex->open();
        $this->catalogCategoryIndex->getTreeCategories()->expandAllCategories();
        $this->catalogCategoryIndex->getTreeCategories()->assignCategory(
            $parentCategory->getName(),
            $childCategory->getName()
        );
        $this->catalogCategoryEdit->getModalBlock()->acceptWarning();

        return [
            'category' => $childCategory,
            'parentCategory' => $parentCategory,
            'childCategory' => $childCategory
        ];
    }
}
