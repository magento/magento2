<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\TestCase\Category;

use Magento\Catalog\Test\Fixture\Category;
use Magento\Catalog\Test\Page\Adminhtml\CatalogCategoryEdit;
use Magento\Catalog\Test\Page\Adminhtml\CatalogCategoryIndex;
use Magento\Mtf\TestCase\Injectable;

/**
 * Precondition:
 * 1. Categories are created
 *
 * Test Flow:
 * 1. Log in to Backend
 * 2. Navigate to the Products>Inventory>Categories
 * 3. Click on 'Add Category' button
 * 4. Fill out all data according to data set
 * 5. Save category
 * 6. Verify created category
 *
 * @group Category_Management
 * @ZephyrId MAGETWO-27319, MAGETWO-21202
 */
class MoveCategoryEntityTest extends Injectable
{
    /* tags */
    const MVP = 'yes';
    /* end tags */

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
     * @return void
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
     * @param array|null $moveLevel
     * @return array
     */
    public function test(Category $childCategory, Category $parentCategory, array $moveLevel = null)
    {
        // Preconditions:
        $parentCategory->persist();
        $childCategory->persist();
        $bottomChildCategory = $childCategory;

        if ($moveLevel !== null) {
            for ($nestingIterator = 1; $nestingIterator < $moveLevel['child']; $nestingIterator++) {
                $childCategory = $childCategory->getDataFieldConfig('parent_id')['source']->getParentCategory();
            }
        }

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
            'childCategory' => $childCategory,
            'bottomChildCategory' => $bottomChildCategory,
        ];
    }
}
