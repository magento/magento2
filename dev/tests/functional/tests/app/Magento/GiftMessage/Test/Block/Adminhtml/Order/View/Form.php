<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GiftMessage\Test\Block\Adminhtml\Order\View;

use Magento\Mtf\Block\Form as ParentForm;

/**
 * Class Form
 * Backend item form for gift message.
 */
class Form extends ParentForm
{
    /**
     * Selector for 'Close' button.
     *
     * @var string
     */
    protected $closeButton = '.ui-dialog-titlebar-close';

    /**
     * Close form dialog.
     *
     * @return void
     */
    public function closeDialog()
    {
        $this->_rootElement->find($this->closeButton)->click();
    }
}
