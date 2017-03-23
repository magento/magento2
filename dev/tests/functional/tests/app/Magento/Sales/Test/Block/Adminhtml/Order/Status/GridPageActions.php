<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Block\Adminhtml\Order\Status;

/**
 * Class GridPageActions
 * Grid page actions block on OrderStatus index page
 */
class GridPageActions extends \Magento\Backend\Test\Block\GridPageActions
{
    /**
     * "Assign Status To state" button
     *
     * @var string
     */
    protected $assignButton = '#assign';

    /**
     * Click on "Assign Status To State" button
     *
     * @return void
     */
    public function assignStatusToState()
    {
        $this->_rootElement->find($this->assignButton)->click();
    }
}
