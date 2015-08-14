<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model\Observer;

class GridSynchronization
{
    /** @var \Magento\Customer\Model\Resource\Customer\GridSynchronization  */
    protected $gridSynchronization;

    /**
     * @param \Magento\Customer\Model\Resource\Customer\GridSynchronization $gridSynchronization
     */
    public function __construct(
        \Magento\Customer\Model\Resource\Customer\GridSynchronization $gridSynchronization
    ) {
        $this->gridSynchronization = $gridSynchronization;
    }

    /**
     * @return void
     */
    public function syncCustomerGrid()
    {
        $this->gridSynchronization->syncCustomerGrid();
    }
}

