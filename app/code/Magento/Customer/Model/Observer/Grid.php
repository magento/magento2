<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model\Observer;

class Grid
{
    /** @var \Magento\Customer\Model\Resource\Customer\Grid */
    protected $gridSynchronization;

    /**
     * @param \Magento\Customer\Model\Resource\Customer\Grid $grid
     */
    public function __construct(
        \Magento\Customer\Model\Resource\Customer\Grid $grid
    ) {
        $this->gridSynchronization = $grid;
    }

    /**
     * @return void
     */
    public function syncCustomerGrid()
    {
        $this->gridSynchronization->syncCustomerGrid();
    }
}

