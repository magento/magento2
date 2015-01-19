<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Block\Adminhtml\Order;

/**
 * Class StatusGrid
 * Backend sales order's status management grid
 */
class StatusGrid extends \Magento\Backend\Test\Block\Widget\Grid
{
    /**
     * Filters array mapping
     */
    protected $filters = [
        'label' => [
            'selector' => '#sales_order_status_grid_filter_label',
        ],
        'status' => [
            'selector' => '#sales_order_status_grid_filter_status',
        ],
        'state' => [
            'selector' => '#sales_order_status_grid_filter_state',
        ],
    ];

    /**
     * Locator value for link in action column
     *
     * @var string
     */
    protected $editLink = '[data-column="label"]';

    /**
     * Selector for unassign custom status link
     *
     * @var string
     */
    protected $unassignLink = '[data-column="unassign"] a';

    /**
     * Search custom status and unassign it
     *
     * @param array $filter
     * @throws \Exception
     * @return void
     */
    public function searchAndUnassign(array $filter)
    {
        $this->openFilterBlock();
        $this->search($filter);
        $selectItem = $this->_rootElement->find($this->unassignLink);
        if ($selectItem->isVisible()) {
            $selectItem->click();
        } else {
            throw new \Exception('Searched item was not found.');
        }
    }
}
