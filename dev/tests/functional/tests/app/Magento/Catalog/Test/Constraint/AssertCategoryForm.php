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
use Magento\Mtf\Client\Locator;
use Magento\Mtf\Client\BrowserInterface;
use Magento\Mtf\Block\BlockFactory;

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
        'store_id'
    ];

    /**
     * Default sore switcher block locator.
     *
     * @var string
     */
    private $storeSwitcherBlock = '.store-switcher';

    /**
     * Dropdown block locator.
     *
     * @var string
     */
    private $dropdownBlock = '.dropdown';

    /**
     * Selector for confirm.
     *
     * @var string
     */
    private $confirmModal = '.confirm._show[data-role=modal]';

    /**
     * Assert that displayed category data on edit page equals passed from fixture.
     *
     * @param CatalogCategoryIndex $catalogCategoryIndex
     * @param CatalogCategoryEdit $catalogCategoryEdit
     * @param Category $category
     * @param BrowserInterface $browser
     * @param BlockFactory $blockFactory
     * @return void
     */
    public function processAssert(
        CatalogCategoryIndex $catalogCategoryIndex,
        CatalogCategoryEdit $catalogCategoryEdit,
        Category $category,
        BrowserInterface $browser,
        BlockFactory $blockFactory
    ) {
        $catalogCategoryIndex->open();
        $catalogCategoryIndex->getTreeCategories()->selectCategory($category, true);
        $this->switchScope($category, $browser, $blockFactory);
        $fixtureData = $this->prepareFixtureData($category->getData());
        $formData = $catalogCategoryEdit->getEditForm()->getData($category);
        $error = $this->verifyData($this->sortData($fixtureData), $this->sortData($formData));
        \PHPUnit_Framework_Assert::assertEmpty($error, $error);
    }

    /**
     * Switches scope to selected store on the edit form
     *
     * @param Category $category
     * @param BrowserInterface $browser
     * @param BlockFactory $blockFactory
     * @return void
     */
    private function switchScope(Category $category, BrowserInterface $browser, BlockFactory $blockFactory)
    {
        if ($category->hasData('store_id')) {
            $store = $category->getStoreId()['source']->getName();
            $storeSwitcherBlock = $browser->find($this->storeSwitcherBlock);
            $storeSwitcherBlock->find($this->dropdownBlock, Locator::SELECTOR_CSS, 'liselectstore')->setValue($store);
            $modalElement = $browser->find($this->confirmModal);
            /** @var \Magento\Ui\Test\Block\Adminhtml\Modal $modal */
            $modal = $blockFactory->create(
                \Magento\Ui\Test\Block\Adminhtml\Modal::class,
                ['element' => $modalElement]
            );
            $modal->acceptAlert();
        }
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
