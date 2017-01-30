<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Block\Adminhtml\Order\View\Tab;

use Magento\Backend\Test\Block\Widget\Tab;
use Magento\Sales\Test\Block\Adminhtml\Order\View\Tab\Invoices\Grid;

/**
 * Class Invoices
 * Invoices tab
 */
class Invoices extends Tab
{
    /**
     * Grid block css selector
     *
     * @var string
     */
    protected $grid = '#sales_order_view_tabs_order_invoices_content';

    /**
     * Get grid block
     *
     * @return Grid
     */
    public function getGridBlock()
    {
        return $this->blockFactory->create(
            'Magento\Sales\Test\Block\Adminhtml\Order\View\Tab\Invoices\Grid',
            ['element' => $this->_rootElement->find($this->grid)]
        );
    }
}
