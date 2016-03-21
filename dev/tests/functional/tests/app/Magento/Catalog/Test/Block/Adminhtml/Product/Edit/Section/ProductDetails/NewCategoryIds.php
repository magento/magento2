<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Block\Adminhtml\Product\Edit\Section\ProductDetails;

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
    protected $buttonNewCategory = '[data-index="create_category_button"]';

    /**
     * Modal slide "New Category".
     *
     * @var string
     */
    protected $createCategoryModal = '.product_form_product_form_create_category_modal';

    /**
     * Button "Create Category" on dialog box.
     *
     * @var string
     */
    protected $createCategoryButton = '#save';

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
        $this->waitForElementVisible($this->createCategoryModal);
    }
}
