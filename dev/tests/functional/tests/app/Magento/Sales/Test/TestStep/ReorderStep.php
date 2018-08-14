<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\TestStep;

use Magento\Sales\Test\Page\Adminhtml\SalesOrderView;
use Magento\Mtf\TestStep\TestStepInterface;

/**
 * Class ReorderStep
 * Click reorder from order on backend
 */
class ReorderStep implements TestStepInterface
{
    /**
     * Order View Page
     *
     * @var SalesOrderView
     */
    protected $salesOrderView;

    /**
     * @construct
     * @param SalesOrderView $salesOrderView
     */
    public function __construct(SalesOrderView $salesOrderView)
    {
        $this->salesOrderView = $salesOrderView;
    }

    /**
     * Click reorder
     *
     * @return void
     */
    public function run()
    {
        $this->salesOrderView->getPageActions()->reorder();
    }
}
