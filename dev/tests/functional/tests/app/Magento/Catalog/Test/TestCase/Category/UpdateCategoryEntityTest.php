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
use Magento\Mtf\Fixture\FixtureFactory;

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
     * Fixture Factory.
     *
     * @var FixtureFactory
     */
    protected $fixtureFactory;

    /**
     * Inject page end prepare default category
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
     * Test for update category
     *
     * @param Category $category
     * @param Category $initialCategory
     * @return array
     */
    public function test(Category $category, Category $initialCategory)
    {
        $initialCategory->persist();
        $this->catalogCategoryIndex->open();
        $this->catalogCategoryIndex->getTreeCategories()->selectCategory($initialCategory);
        $this->catalogCategoryEdit->getEditForm()->fill($category);
        $this->catalogCategoryEdit->getFormPageActions()->save();

        return ['category' => $this->prepareCategory($category, $initialCategory)];
    }

    /**
     * Prepare Category fixture with the updated data.
     *
     * @param Category $category
     * @param Category $initialCategory
     * @return Category
     */
    protected function prepareCategory(Category $category, Category $initialCategory)
    {
        $parentCategory = $category->hasData('parent_id')
            ? $category->getDataFieldConfig('parent_id')['source']->getParentCategory()
            : $initialCategory->getDataFieldConfig('parent_id')['source']->getParentCategory();

        $rewriteData = ['parent_id' => ['source' => $parentCategory]];
        if ($category->hasData('store_id')) {
            $rewriteData['store_id'] = ['source' => $category->getDataFieldConfig('store_id')['source']->getStore()];
        }

        $data = [
            'data' => array_merge(
                $initialCategory->getData(),
                $category->getData(),
                $rewriteData
            )
        ];

        return $this->fixtureFactory->createByCode('category', $data);
    }
}
