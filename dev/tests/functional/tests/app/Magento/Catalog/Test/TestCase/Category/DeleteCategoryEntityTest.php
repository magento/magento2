<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Catalog\Test\TestCase\Category;

use Magento\Catalog\Test\Fixture\CatalogCategory;
use Magento\Catalog\Test\Page\Adminhtml\CatalogCategoryEdit;
use Magento\Catalog\Test\Page\Adminhtml\CatalogCategoryIndex;
use Mtf\TestCase\Injectable;

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
 * @group Category_Management_(MX)
 * @ZephyrId MAGETWO-23303
 */
class DeleteCategoryEntityTest extends Injectable
{
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
     * Delete category
     *
     * @param CatalogCategory $category
     * @return void
     */
    public function test(CatalogCategory $category)
    {
        $category->persist();
        $this->catalogCategoryIndex->open();
        $this->catalogCategoryIndex->getTreeCategories()->selectCategory($category);
        $this->catalogCategoryEdit->getFormPageActions()->delete();
    }
}
