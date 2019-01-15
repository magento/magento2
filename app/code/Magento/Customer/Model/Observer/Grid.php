<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model\Observer;

use Magento\Customer\Model\ResourceModel\Customer\Grid as CustomerGrid;

/**
 * @deprecated 100.1.0
 */
class Grid
{
    /**
     * @var CustomerGrid
     */
    protected $customerGrid;

    /**
     * @param CustomerGrid $grid
     */
    public function __construct(
        CustomerGrid $grid
    ) {
        $this->customerGrid = $grid;
    }

    /**
     * @return void
     *
     * @deprecated 100.1.0
     */
    public function syncCustomerGrid()
    {
        $this->customerGrid->syncCustomerGrid();
    }
}
