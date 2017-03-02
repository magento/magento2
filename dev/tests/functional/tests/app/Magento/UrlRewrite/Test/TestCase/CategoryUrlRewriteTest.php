<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\UrlRewrite\Test\TestCase;

use Magento\Catalog\Test\Page\Adminhtml\CatalogCategoryIndex;
use Magento\Catalog\Test\Page\Adminhtml\CatalogCategoryEdit;
use Magento\Catalog\Test\Fixture\Category;
use Magento\Mtf\TestCase\Injectable;
use Magento\Store\Test\Fixture\Store;

/**
 * Test for Url rewrites in catalog categories after changing url key for store view and moving category.
 *
 * Preconditions:
 * 1. Create additional Store View in Main Website Store.
 * 2. Create sub-categories "first-test" and "second-test" in Default Category.
 * 3. Add one or more any products to created sub-categories.
 * 4. Reindex and clean caches.
 *
 * Steps:
 * 1. Log in to backend.
 * 2. Navigate to Products > Categories.
 * 3. On the categories editing page change store view to created additional view.
 * 4. Change URL key for category "first-test" from default to "first-test-2". Save.
 * 5. Change store view to "All store views".
 * 6. Move category "first-test" inside "second-test".
 * 7. Perform all assertions.
 *
 * @ZephyrId MAGETWO-45385
 */
class CategoryUrlRewriteTest extends Injectable
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
     * @param Store $storeView
     * @param Category $childCategory
     * @param Category $parentCategory
     * @param Category $categoryUpdates
     * @return array
     */
    public function test(Store $storeView, Category $childCategory, Category $parentCategory, Category $categoryUpdates)
    {
        // Preconditions:
        $storeView->persist();
        $parentCategory->persist();
        $childCategory->persist();

        // Steps:
        $this->catalogCategoryIndex->open();
        $this->catalogCategoryIndex->getTreeCategories()->selectCategory($childCategory);
        $this->catalogCategoryEdit->getFormPageActions()->selectStoreView($storeView->getName());
        $this->catalogCategoryEdit->getEditForm()->fill($categoryUpdates);
        $this->catalogCategoryEdit->getFormPageActions()->save();
        $this->catalogCategoryIndex->getTreeCategories()->assignCategory(
            $parentCategory->getName(),
            $childCategory->getName()
        );
        if ($this->catalogCategoryEdit->getModalBlock()->isVisible()) {
            $this->catalogCategoryEdit->getModalBlock()->acceptWarning();
        }

        return [
            'storeView' => $storeView,
            'childCategory' => $childCategory,
            'parentCategory' => $parentCategory
        ];
    }
}
