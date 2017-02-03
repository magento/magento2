<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Block\Adminhtml\Product\Edit\Tab\ProductDetails;

use Magento\Catalog\Test\Fixture\Category;
use Magento\Mtf\Block\Form;

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
    protected $createCategoryButton = '[data-role="action"][type="button"]';

    /**
     * Add new category to product.
     *
     * @param Category $category
     * @return void
     */
    public function addNewCategory(Category $category)
    {
        $data = [
            'name' => $category->getName(),
            'parent_category' => $category->getDataFieldConfig('parent_id')['source']->getParentCategory()->getName()
        ];

        $this->openNewCategoryDialog();
        $this->_fill($this->dataMapping($data));

        $this->_rootElement->find($this->createCategoryButton)->click();
        $this->waitForElementNotVisible($this->createCategoryButton);
    }

    /**
     * Open new category dialog.
     *
     * @return void
     */
    protected function openNewCategoryDialog()
    {
        $this->browser->find($this->buttonNewCategory)->click();
        $this->waitForElementVisible($this->createCategoryDialog);
    }
}
