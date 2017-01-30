<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Block\Adminhtml\Order\Invoice;

use Magento\Sales\Test\Block\Adminhtml\Order\AbstractForm;
use Magento\Sales\Test\Block\Adminhtml\Order\Invoice\Form\Items;

/**
 * Class Form
 * Invoice create form
 */
class Form extends AbstractForm
{
    /**
     * Items block css selector
     *
     * @var string
     */
    protected $items = '#invoice_item_container';

    /**
     * Get items block
     *
     * @return Items
     */
    public function getItemsBlock()
    {
        return $this->blockFactory->create(
            'Magento\Sales\Test\Block\Adminhtml\Order\Invoice\Form\Items',
            ['element' => $this->_rootElement->find($this->items)]
        );
    }
}
