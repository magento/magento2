<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\TestCase\Category;

use Magento\Catalog\Test\Fixture\Category;
use Magento\Catalog\Test\Page\Adminhtml\CatalogCategoryEdit;
use Magento\Catalog\Test\Page\Adminhtml\CatalogCategoryIndex;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\TestCase\Injectable;

/**
 * Test Creation for CreateCategoryEntity.
 *
 * Test Flow:
 * 1. Login as admin
 * 2. Navigate to the Products>Inventory>Categories
 * 3. Click on 'Add Category' button
 * 4. Fill out all data according to data set
 * 5. Save category
 * 6. Verify created category
 *
 * @group Category_Management_(MX)
 * @ZephyrId MAGETWO-23411
 */
class CreateCategoryEntityTest extends Injectable
{
    /* tags */
    const MVP = 'yes';
    const DOMAIN = 'MX';
    const TEST_TYPE = 'acceptance_test';
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
     * Fixture create factory.
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
        $this->catalogCategoryIndex = $catalogCategoryIndex;
        $this->catalogCategoryEdit = $catalogCategoryEdit;
        $this->fixtureFactory = $fixtureFactory;
    }

    /**
     * Create category.
     *
     * @param Category $category
     * @param string $addCategory
     * @return array
     */
    public function test(Category $category, $addCategory)
    {
        $this->catalogCategoryIndex->open();
        $this->catalogCategoryIndex->getTreeCategories()->selectCategory($category, false);
        $this->catalogCategoryIndex->getTreeCategories()->$addCategory();
        $this->catalogCategoryEdit->getEditForm()->fill($category);
        $this->catalogCategoryEdit->getFormPageActions()->save();
        $categoryId = $this->catalogCategoryEdit->getEditForm()->getCategoryId();

        return ['category' => $this->prepareCategory($category, $categoryId)];
    }

    /**
     *  Prepare category fixture data.
     *
     * @param Category $category
     * @param string $categoryId
     * @return Category
     */
    private function prepareCategory(Category $category, $categoryId)
    {
        $parentCategory = null;
        $cmsBlock = null;
        $store = null;
        $products = null;

        $parentSource = $category->getDataFieldConfig('parent_id')['source'];
        if (is_a($parentSource, Category\ParentId::class) && $parentSource->getParentCategory()) {
            $parentCategory = $parentSource->getParentCategory();
        }

        $cmsBlockSource = $category->getDataFieldConfig('landing_page')['source'];
        if (is_a($cmsBlockSource, Category\LandingPage::class) && $cmsBlockSource->getCmsBlock()) {
            $cmsBlock = $cmsBlockSource->getCmsBlock();
        }

        $storeSource = $category->getDataFieldConfig('store_id')['source'];
        if (is_a($storeSource, Category\StoreId::class) && $storeSource->getStore()) {
            $store = $storeSource->getStore();
        }

        $productSource = $category->getDataFieldConfig('category_products')['source'];
        if (is_a($productSource, Category\CategoryProducts::class) && $productSource->getProducts()) {
            $products = $productSource->getProducts();
        }

        $data = array_merge(
            $category->getData(),
            ['id' => $categoryId],
            ['parent_id' => ['source' => $parentCategory]],
            ['landing_page' => ['source' => $cmsBlock]],
            ['store_id' => ['source' => $store]],
            ['category_products' => ['products' => $products]]
        );

        return $this->fixtureFactory->create(Category::class, ['data' => $data]);
    }
}
