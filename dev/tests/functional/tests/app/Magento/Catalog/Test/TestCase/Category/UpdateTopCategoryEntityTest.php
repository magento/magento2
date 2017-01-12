<?php
/**
 * Copyright © 2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\TestCase\Category;

use Magento\Catalog\Test\Fixture\Category;
use Magento\Catalog\Test\Page\Adminhtml\CatalogCategoryEdit;
use Magento\Catalog\Test\Page\Adminhtml\CatalogCategoryIndex;
use Magento\Mtf\TestCase\Injectable;
use Magento\Mtf\Fixture\FixtureFactory;

/**
 * Preconditions:
 * 1. Create category.
 *
 * Steps:
 * 1. Navigate Products->Categories.
 * 2. Open category created in preconditions.
 * 3. Update data according to data set.
 * 4. Save category.
 * 5. Perform assertions.
 *
 * @group Category_Management
 * @ZephyrId MAGETWO-27327
 */
class UpdateTopCategoryEntityTest extends Injectable
{
    /* tags */
    const MVP = 'yes';
    /* end tags */

    /**
     * Catalog category index page.
     *
     * @var CatalogCategoryIndex
     */
    protected $catalogCategoryIndex;

    /**
     * Catalog category edit page.
     *
     * @var CatalogCategoryEdit
     */
    protected $catalogCategoryEdit;

    /**
     * Fixture Factory.
     *
     * @var FixtureFactory
     */
    protected $fixtureFactory;

    /**
     * Inject pages.
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
        $this->fixtureFactory = $fixtureFactory;
        $this->catalogCategoryIndex = $catalogCategoryIndex;
        $this->catalogCategoryEdit = $catalogCategoryEdit;
    }

    /**
     * Top parent category update test.
     *
     * @param Category $category
     * @param Category $initialCategory
     * @param int $nestingLevel
     * @return array
     */
    public function test(
        Category $category,
        Category $initialCategory,
        $nestingLevel
    ) {
        $initialCategory->persist();
        $topCategory =  $this->getParentCategoryByNestingLevel($initialCategory, $nestingLevel);
        $this->catalogCategoryIndex->open();
        $this->catalogCategoryIndex->getTreeCategories()->selectCategory($topCategory);
        $this->catalogCategoryEdit->getEditForm()->fill($category);
        $this->catalogCategoryEdit->getFormPageActions()->save();

        $categories = [];
        $categoriesBeforeSave = [];
        $this->getCategoryFixture($categories, $initialCategory, $category->getData(), $nestingLevel);
        $this->getCategory($initialCategory, $categoriesBeforeSave, $nestingLevel);

        return [
            'categories' => $categories,
            'categoriesBeforeSave' => $categoriesBeforeSave
        ];
    }

    /**
     * Get category fixture after saving in the admin panel.
     *
     * @param array $categories
     * @param Category $currentCategory
     * @param array $data
     * @param int $nestingLevel
     * @return Category
     */
    private function getCategoryFixture(array &$categories, Category $currentCategory, array $data, int $nestingLevel)
    {
        if (--$nestingLevel) {
            $parentCategory = $this->getCategoryFixture(
                $categories,
                $currentCategory->getDataFieldConfig('parent_id')['source']->getParentCategory(),
                $data,
                $nestingLevel
            );
            $category = $this->fixtureFactory->createByCode(
                'category',
                ['data' => array_merge($currentCategory->getData(), ['parent_id' => ['source' => $parentCategory]])]
            );
        } else {
            $category = $this->fixtureFactory->createByCode(
                'category',
                ['data' => array_merge($currentCategory->getData(), $data)]
            );
        }
        $categories[$nestingLevel + 1] = $category;
        return $category;
    }

    /**
     * Get category before it was saved in the admin panel.
     *
     * @param Category $initialCategory
     * @param array $categoriesBeforeSave
     * @param int $nestingLevel
     * @return Category
     */
    private function getCategory(Category $initialCategory, &$categoriesBeforeSave, $nestingLevel)
    {
        if (--$nestingLevel) {
            $parentCategory = $this->getCategory(
                $initialCategory->getDataFieldConfig('parent_id')['source']->getParentCategory(),
                $categoriesBeforeSave,
                $nestingLevel
            );
            $category = $this->fixtureFactory->createByCode(
                'category',
                ['data' => array_merge($initialCategory->getData(), ['parent_id' => ['source' => $parentCategory]])]
            );
        } else {
            $category = $initialCategory;
        }
        $categoriesBeforeSave[$nestingLevel + 1] = $category;
        return $category;
    }

    /**
     * Get parent category by category nesting level.
     *
     * @param Category $category
     * @param int $nestingLevel
     * @return Category
     */
    private function getParentCategoryByNestingLevel(Category $category, $nestingLevel)
    {
        for ($nestingIterator = 1; $nestingIterator < $nestingLevel; $nestingIterator++) {
            $category = $category->getDataFieldConfig('parent_id')['source']->getParentCategory();
        }

        return $category;
    }
}
