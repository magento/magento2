``<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Block\Adminhtml\Product\Modal;

use Magento\Ui\Test\Block\Adminhtml\FormSections;

/**
 * New product attribute form.
 */
class NewAttribute extends FormSections
{
    /**
     * "Save" button.
     *
     * @var string
     */
    protected $save = 'button#save';

    /**
     * Click "Save Attribute" button on attribute form..
     *
     * @return void
     */
    public function saveAttribute()
    {
        $this->_rootElement->find($this->save)->click();
    }
}
