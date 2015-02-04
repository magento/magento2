<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Block\Adminhtml\Product\Edit\Tab\ProductDetails;

use Magento\Mtf\Client\Locator;
use Magento\Mtf\Fixture\FixtureInterface;
use Magento\Catalog\Test\Fixture\Category;
use Magento\Backend\Test\Block\Widget\FormTabs;

/**
 * Create new category.
 */
class NewCategoryIds extends FormTabs
{
    /**
     * Button "New Category".
     *
     * @var string
     */
    protected $buttonNewCategory = '#add_category_button';

    /**
     * Dialog box "Create Category".
     *
     * @var string
     */
    protected $createCategoryDialog = './/ancestor::body//*[contains(@class,"mage-new-category-dialog")]';

    /**
     * "Parent Category" block on dialog box.
     *
     * @var string
     */
    protected $parentCategoryBlock = '//*[contains(@class,"field-new_category_parent")]';

    /**
     * Field "Category Name" on dialog box.
     *
     * @var string
     */
    protected $fieldNewCategoryName = '//input[@id="new_category_name"]';

    /**
     * Button "Create Category" on dialog box.
     *
     * @var string
     */
    protected $createCategoryButton = '//button[contains(@class,"action-create")]';

    /**
     * Save new category.
     *
     * @param FixtureInterface $fixture
     * @return void
     */
    public function addNewCategory(FixtureInterface $fixture)
    {
        $categoryName = $fixture->getName();
        $parentCategory = $fixture->getDataFieldConfig('parent_id')['source']->getParentCategory()->getName();

        $this->openNewCategoryDialog();
        $this->_rootElement->find(
            $this->createCategoryDialog . $this->fieldNewCategoryName,
            Locator::SELECTOR_XPATH
        )->setValue($categoryName);

        $this->selectParentCategory($parentCategory);

        $buttonCreateCategory = $this->createCategoryDialog . $this->createCategoryButton;
        $this->_rootElement->find($buttonCreateCategory, Locator::SELECTOR_XPATH)->click();
        $this->waitForElementNotVisible($buttonCreateCategory, Locator::SELECTOR_XPATH);
    }

    /**
     * Select parent category for new one.
     *
     * @param string $categoryName
     * @return void
     */
    protected function selectParentCategory($categoryName)
    {
        $this->_rootElement->find(
            $this->createCategoryDialog . $this->parentCategoryBlock,
            Locator::SELECTOR_XPATH,
            '\Magento\Catalog\Test\Block\Adminhtml\Product\Edit\Tab\ProductDetails\ParentCategoryIds'
        )->setValue($categoryName);
    }

    /**
     * Open new category dialog.
     *
     * @return void
     */
    protected function openNewCategoryDialog()
    {
        $this->_rootElement->find($this->buttonNewCategory)->click();
        $this->waitForElementVisible($this->createCategoryDialog, Locator::SELECTOR_XPATH);
    }
}
