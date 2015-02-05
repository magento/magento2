<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Block\Adminhtml\Product\Edit\Tab\ProductDetails;

use Magento\Mtf\Client\Locator;
use Magento\Catalog\Test\Fixture\Category;
use Magento\Backend\Test\Block\Widget\Form;

/**
 * Create new category.
 */
class NewCategoryIds extends Form
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
    protected $createCategoryDialog = '.mage-new-category-dialog';

    /**
     * "Parent Category" block on dialog box.
     *
     * @var string
     */
    protected $parentCategoryBlock = '.field-new_category_parent';

    /**
     * Button "Create Category" on dialog box.
     *
     * @var string
     */
    protected $createCategoryButton = '.action-create';

    /**
     * Add new category to product.
     *
     * @param Category $category
     * @return void
     */
    public function addNewCategory(Category $category)
    {
        $parentCategory = $category->getDataFieldConfig('parent_id')['source']->getParentCategory()->getName();

        $this->openNewCategoryDialog();
        $this->fill($category);

        $this->selectParentCategory($parentCategory);

        $this->_rootElement->find($this->createCategoryButton)->click();
        $this->waitForElementNotVisible($this->createCategoryButton);
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
            $this->parentCategoryBlock,
            Locator::SELECTOR_CSS,
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
        $this->waitForElementVisible($this->createCategoryDialog);
    }
}
