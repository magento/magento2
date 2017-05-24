<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\TestStep;

use Magento\Sales\Test\Fixture\OrderInjectable;
use Magento\Sales\Test\Page\Adminhtml\OrderIndex;
use Magento\Sales\Test\Page\Adminhtml\SalesOrderView;
use Magento\Mtf\TestStep\TestStepInterface;

/**
 * Unhold order in admin.
 */
class UnholdOrderStep implements TestStepInterface
{
    /**
     * Orders Page.
     *
     * @var OrderIndex
     */
    protected $orderIndex;

    /**
     * Order View Page.
     *
     * @var SalesOrderView
     */
    protected $salesOrderView;

    /**
     * OrderInjectable fixture.
     *
     * @var OrderInjectable
     */
    protected $order;

    /**
     * @construct
     * @param OrderIndex $orderIndex
     * @param SalesOrderView $salesOrderView
     * @param OrderInjectable $order
     */
    public function __construct(OrderIndex $orderIndex, SalesOrderView $salesOrderView, OrderInjectable $order)
    {
        $this->orderIndex = $orderIndex;
        $this->salesOrderView = $salesOrderView;
        $this->order = $order;
    }

    /**
     * On hold order in admin.
     *
     * @return void
     */
    public function run()
    {
        $this->orderIndex->open();
        $this->orderIndex->getSalesOrderGrid()->searchAndOpen(['id' => $this->order->getId()]);
        $this->salesOrderView->getPageActions()->unhold();
    }
}
