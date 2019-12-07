<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\TestCase\Category;

use Magento\Catalog\Test\Fixture\Category;
use Magento\Catalog\Test\Page\Adminhtml\CatalogCategoryEdit;
use Magento\Catalog\Test\Page\Adminhtml\CatalogCategoryIndex;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\TestCase\Injectable;

/**
 * Precondition:
 * 1. Categories are created
 *
 * Test Flow:
 * 1. Log in to Backend
 * 2. Navigate to the Products>Categories
 * 3. Select SubCategory
 * 4. Drag'n'Drop SubCategory
 * 5. Dismiss alert
 * 6. Drag'n'Drop SubCategory
 * 7. Accept alert
 * 8. Save category
 * 9. Verify category
 *
 * @group Category_Management
 * @ZephyrId MAGETWO-27319
 */
class AdvancedMoveCategoryEntityTest extends Injectable
{
    /* tags */
    const MVP = 'no';
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
     * Factory for fixtures.
     *
     * @var FixtureFactory
     */
    private $fixtureFactory;

    /**
     * Inject page end prepare default category.
     *
     * @param CatalogCategoryIndex $catalogCategoryIndex
     * @param CatalogCategoryEdit $catalogCategoryEdit
     * @param FixtureFactory $fixtureFactory
     * @return void
     */
    public function __inject(
        CatalogCategoryIndex $catalogCategoryIndex,
        CatalogCategoryEdit $catalogCategoryEdit,
        FixtureFactory $fixtureFactory
    ) {
        $this->catalogCategoryIndex = $catalogCategoryIndex;
        $this->catalogCategoryEdit = $catalogCategoryEdit;
        $this->fixtureFactory = $fixtureFactory;
    }

    /**
     * Runs test.
     *
     * @param Category $childCategory
     * @param Category $parentCategory
     * @param int|null $moveLevel
     * @return array
     */
    public function test(
        Category $childCategory,
        Category $parentCategory,
        $moveLevel = null
    ) {
        // Preconditions:
        $parentCategory->persist();
        $childCategory->persist();
        $resultCategory = $childCategory;

        if (!empty($moveLevel)) {
            for ($nestingIterator = 1; $nestingIterator < $moveLevel; $nestingIterator++) {
                $childCategory = $childCategory->getDataFieldConfig('parent_id')['source']->getParentCategory();
            }
            $resultCategory = $this->getMovedCategoryTree($resultCategory, $parentCategory, $childCategory);
        }

        // Steps:
        $this->catalogCategoryIndex->open();
        $this->catalogCategoryIndex->getTreeCategories()->expandAllCategories();
        $this->catalogCategoryIndex->getTreeCategories()->selectCategory($childCategory);
        $this->catalogCategoryIndex->getTreeCategories()->assignCategory(
            $parentCategory->getName(),
            $childCategory->getName()
        );
        $this->catalogCategoryEdit->getModalBlock()->dismissWarning();

        $this->catalogCategoryIndex->getTreeCategories()->assignCategory(
            $parentCategory->getName(),
            $childCategory->getName()
        );
        $this->catalogCategoryEdit->getModalBlock()->acceptWarning();
        $this->catalogCategoryEdit->getFormPageActions()->save();

        return [
            'category' => $resultCategory,
            'parentCategory' => $parentCategory,
            'childCategory' => $childCategory,
        ];
    }

    /**
     * Get moved category tree.
     *
     * @param Category $movedCategory
     * @param Category $parentCategory
     * @param Category $childCategory
     * @return Category
     */
    public function getMovedCategoryTree(Category $movedCategory, Category $parentCategory, Category $childCategory)
    {
        $bottomChildCategory = [];
        while ($movedCategory->getName() != $childCategory->getName()) {
            $bottomChildCategory[] = $movedCategory->getData();
            $movedCategory = $movedCategory->getDataFieldConfig('parent_id')['source']->getParentCategory();
        }
        $bottomChildCategory[] = $movedCategory->getData();

        $newCategory = $parentCategory;
        for ($i = count($bottomChildCategory) - 1; $i >= 0; $i--) {
            unset($bottomChildCategory[$i]['parent_id']);
            $bottomChildCategory[$i]['parent_id']['source'] = $newCategory;
            $newCategory = $this->fixtureFactory->createByCode(
                'category',
                ['data' => $bottomChildCategory[$i]]
            );
        }

        return $newCategory;
    }
}
