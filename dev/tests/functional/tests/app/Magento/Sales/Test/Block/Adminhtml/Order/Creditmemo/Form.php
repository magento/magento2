<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Block\Adminhtml\Order\Creditmemo;

use Magento\Sales\Test\Block\Adminhtml\Order\AbstractForm;
use Magento\Sales\Test\Block\Adminhtml\Order\Creditmemo\Form\Items;

/**
 * Credit memo create form.
 */
class Form extends AbstractForm
{
    /**
     * Items block css selector.
     *
     * @var string
     */
    protected $items = '#creditmemo_item_container';

    /**
     * Get items block.
     *
     * @return Items
     */
    public function getItemsBlock()
    {
        return $this->blockFactory->create(
            \Magento\Sales\Test\Block\Adminhtml\Order\Creditmemo\Form\Items::class,
            ['element' => $this->_rootElement->find($this->items)]
        );
    }
}
