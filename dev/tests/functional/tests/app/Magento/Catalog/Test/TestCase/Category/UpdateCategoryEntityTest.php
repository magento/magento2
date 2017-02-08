<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\TestCase\Category;

use Magento\Catalog\Test\Fixture\Category;
use Magento\Catalog\Test\Page\Adminhtml\CatalogCategoryEdit;
use Magento\Catalog\Test\Page\Adminhtml\CatalogCategoryIndex;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\TestCase\Injectable;
use Magento\Mtf\TestStep\TestStepFactory;

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
     * Configuration data.
     *
     * @var string
     */
    protected $configData;

    /**
     * Test step factory.
     *
     * @var TestStepFactory
     */
    protected $testStepFactory;

    /**
     * Fixture create factory.
     *
     * @var FixtureFactory
     */
    protected $fixtureFactory;

    /**
     * Inject page end prepare default category.
     *
     * @param Category $initialCategory
     * @param CatalogCategoryIndex $catalogCategoryIndex
     * @param CatalogCategoryEdit $catalogCategoryEdit
     * @param TestStepFactory $testStepFactory
     * @param FixtureFactory $fixtureFactory
     * @return array
     */
    public function __inject(
        Category $initialCategory,
        CatalogCategoryIndex $catalogCategoryIndex,
        CatalogCategoryEdit $catalogCategoryEdit,
        TestStepFactory $testStepFactory,
        FixtureFactory $fixtureFactory
    ) {
        $this->catalogCategoryIndex = $catalogCategoryIndex;
        $this->catalogCategoryEdit = $catalogCategoryEdit;
        $this->testStepFactory = $testStepFactory;
        $this->fixtureFactory = $fixtureFactory;
        $initialCategory->persist();

        return ['initialCategory' => $initialCategory];
    }

    /**
     * Test for update category.
     *
     * @param Category $category
     * @param Category $initialCategory
     * @param string $configData
     * @return array
     */
    public function test(Category $category, Category $initialCategory, $configData = null)
    {
        $this->configData = $configData;

        // Preconditions
        $this->testStepFactory->create(
            \Magento\Config\Test\TestStep\SetupConfigurationStep::class,
            ['configData' => $this->configData]
        )->run();

        // Process steps
        $this->catalogCategoryIndex->open();
        $this->catalogCategoryIndex->getTreeCategories()->selectCategory($initialCategory);
        $this->catalogCategoryEdit->getEditForm()->fill($category);
        $this->catalogCategoryEdit->getFormPageActions()->save();

        return ['category' => $this->prepareCategory($category, $initialCategory)];
    }

    /**
     *  Prepare category fixture with updated data.
     *
     * @param Category $category
     * @param Category $initialCategory
     * @return Category
     */
    private function prepareCategory(Category $category, Category $initialCategory)
    {
        $parentCategory = null;
        $cmsBlock = null;
        $store = null;

        foreach ([$initialCategory, $category] as $item) {
            $parentSource = $item->getDataFieldConfig('parent_id')['source'];
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
        }

        $data = array_merge(
            $initialCategory->getData(),
            $category->getData(),
            ['parent_id' => ['source' => $parentCategory]],
            ['landing_page' => ['source' => $cmsBlock]],
            ['store_id' => ['source' => $store]]
        );

        return $this->fixtureFactory->create(Category::class, ['data' => $data]);
    }

    /**
     * Clean data after running test.
     *
     * @return void
     */
    public function tearDown()
    {
        $this->testStepFactory->create(
            \Magento\Config\Test\TestStep\SetupConfigurationStep::class,
            ['configData' => $this->configData, 'rollback' => true]
        )->run();
    }
}
