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
     * "Create New Attribute" button.
     *
     * @var string
     */
    protected $createNewAttribute = 'button[data-index="add_new_attribute_button"]';

    /**
     * Create new attribute.
     *
     * @return void
     */
    public function createNewAttribute()
    {
        $this->_rootElement->find($this->createNewAttribute)->click();
    }
}
