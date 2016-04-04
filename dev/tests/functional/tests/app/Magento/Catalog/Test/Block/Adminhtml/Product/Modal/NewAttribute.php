<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Block\Adminhtml\Product\Modal;

use Magento\Ui\Test\Block\Adminhtml\FormSections;

/**
 * Product new attribute modal.
 */
class NewAttribute extends FormSections
{
    /**
     * Selector for "Save" button.
     *
     * @var string
     */
    private $save = 'button#save';

    /**
     * Click "Save Attribute" button on attribute form.
     *
     * @return void
     */
    public function saveAttribute()
    {
        $this->_rootElement->find($this->save)->click();
    }
}
