<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model\Observer;

use Magento\Customer\Model\ResourceModel\Customer\Grid as CustomerGrid;

/**
 * @deprecated 2.1.0
 * @since 2.0.0
 */
class Grid
{
    /**
     * @var CustomerGrid
     * @since 2.0.0
     */
    protected $customerGrid;

    /**
     * @param CustomerGrid $grid
     * @since 2.0.0
     */
    public function __construct(
        CustomerGrid $grid
    ) {
        $this->customerGrid = $grid;
    }

    /**
     * @return void
     *
     * @deprecated 2.1.0
     * @since 2.0.0
     */
    public function syncCustomerGrid()
    {
        $this->customerGrid->syncCustomerGrid();
    }
}
