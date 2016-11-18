<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\TestStep;

use Magento\Sales\Test\Fixture\OrderInjectable;
use Magento\Sales\Test\Page\Adminhtml\OrderIndex;
use Magento\Mtf\TestStep\TestStepInterface;
use Magento\Sales\Test\Page\Adminhtml\SalesOrderView;

/**
 * Void authorization for created order.
 */
class VoidAuthorizationStep implements TestStepInterface
{
    /**
     * Sales order index page.
     *
     * @var OrderIndex
     */
    protected $orderIndex;

    /**
     * Order instance.
     *
     * @var OrderInjectable
     */
    protected $order;

    /**
     * Order view page.
     *
     * @var SalesOrderView
     */
    private $salesOrderView;

    /**
     * @param OrderInjectable $order
     * @param OrderIndex $orderIndex
     * @param SalesOrderView $salesOrderView
     */
    public function __construct(OrderInjectable $order, OrderIndex $orderIndex, SalesOrderView $salesOrderView)
    {
        $this->orderIndex = $orderIndex;
        $this->order = $order;
        $this->salesOrderView = $salesOrderView;
    }

    /**
     * Void authorization.
     *
     * @return void
     */
    public function run()
    {
        $this->orderIndex->open();
        $this->orderIndex->getSalesOrderGrid()->searchAndOpen(['id' => $this->order->getId()]);
        $this->salesOrderView->getPageActions()->void();
    }
}
