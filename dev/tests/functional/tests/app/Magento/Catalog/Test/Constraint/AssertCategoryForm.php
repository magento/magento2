<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Constraint;

use Magento\Catalog\Test\Fixture\Category;
use Magento\Catalog\Test\Page\Adminhtml\CatalogCategoryEdit;
use Magento\Catalog\Test\Page\Adminhtml\CatalogCategoryIndex;
use Magento\Mtf\Constraint\AbstractAssertForm;

/**
 * Assert that displayed category data on edit page equals passed from fixture.
 */
class AssertCategoryForm extends AbstractAssertForm
{
    /* tags */
    const SEVERITY = 'low';
    /* end tags */

    /**
     * List skipped fixture fields in verify.
     *
     * @var array
     */
    protected $skippedFixtureFields = [
        'parent_id',
        'id',
        'store_id',
    ];

    /**
     * Assert that displayed category data on edit page equals passed from fixture.
     *
     * @param CatalogCategoryIndex $catalogCategoryIndex
     * @param CatalogCategoryEdit $catalogCategoryEdit
     * @param Category $category
     * @return void
     */
    public function processAssert(
        CatalogCategoryIndex $catalogCategoryIndex,
        CatalogCategoryEdit $catalogCategoryEdit,
        Category $category
    ) {
        $catalogCategoryIndex->open();
        $catalogCategoryIndex->getTreeCategories()->selectCategory($category, true);
        if ($category->hasData('store_id')) {
            $storeName = $category->getStoreId()['source']->getName();
            $catalogCategoryEdit->getFormPageActions()->selectStoreView($storeName);
        }
        $fixtureData = $this->prepareFixtureData($category->getData());
        $formData = $catalogCategoryEdit->getEditForm()->getData($category);
        $error = $this->verifyData($this->sortData($fixtureData), $this->sortData($formData));
        \PHPUnit_Framework_Assert::assertEmpty($error, $error);
    }

    /**
     * Prepares fixture data for comparison.
     *
     * @param array $data
     * @return array
     */
    protected function prepareFixtureData(array $data)
    {
        if (!isset($data['parent_id'])) {
            $this->skippedFixtureFields[] = 'url_key';
        }

        if (isset($data['url_key'])) {
            $data['url_key'] = strtolower($data['url_key']);
        }

        return array_diff_key($data, array_flip($this->skippedFixtureFields));
    }

    /**
     * Sort data for comparison.
     *
     * @param array $data
     * @return array
     */
    protected function sortData(array $data)
    {
        if (isset($data['available_sort_by'])) {
            $data['available_sort_by'] = array_values($data['available_sort_by']);
            sort($data['available_sort_by']);
        }

        if (isset($data['category_products'])) {
            sort($data['category_products']);
        }

        return $data;
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Category data on edit page equals to passed from fixture.';
    }
}
