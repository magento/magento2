<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Block\Adminhtml\Product\Modal;

use Magento\Ui\Test\Block\Adminhtml\FormSections;

/**
 * Add new attribute modal.
 */
class AddAttribute extends FormSections
{
    /**
     * Selector for "Create New Attribute" button.
     *
     * @var string
     */
    private $createNewAttribute = 'button[data-index="add_new_attribute_button"]';

    /**
     * Click on "Create new attribute" button.
     *
     * @return void
     */
    public function createNewAttribute()
    {
        $this->_rootElement->find($this->createNewAttribute)->click();
    }
}
