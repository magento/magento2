<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Block\Adminhtml\Order\View\Tab;

use Magento\Backend\Test\Block\Widget\Tab;
use Magento\Sales\Test\Block\Adminhtml\Order\View\Tab\Shipments\Grid;

/**
 * Shipments tab.
 */
class Shipments extends Tab
{
    /**
     * Grid block css selector.
     *
     * @var string
     */
    protected $grid = '#sales_order_view_tabs_order_shipments_content';

    /**
     * Get grid block.
     *
     * @return Grid
     */
    public function getGridBlock()
    {
        return $this->blockFactory->create(
            'Magento\Sales\Test\Block\Adminhtml\Order\View\Tab\Shipments\Grid',
            ['element' => $this->_rootElement->find($this->grid)]
        );
    }
}
