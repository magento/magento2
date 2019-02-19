<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\TestStep;

use Magento\Mtf\TestStep\TestStepInterface;
use Magento\Sales\Test\Fixture\OrderInjectable;
use Magento\Sales\Test\Page\Adminhtml\OrderIndex;
use Magento\Sales\Test\Page\Adminhtml\SalesOrderView;

/**
 * Click cancel from order in Admin.
 */
class CancelOrderStep implements TestStepInterface
{
    /**
     * Sales order index page.
     *
     * @var OrderIndex
     */
    private $orderIndex;

    /**
     * Order instance.
     *
     * @var OrderInjectable
     */
    private $order;

    /**
     * Order View Page.
     *
     * @var SalesOrderView
     */
    protected $salesOrderView;

    /**
     * @param OrderIndex $orderIndex
     * @param OrderInjectable $order
     * @param SalesOrderView $salesOrderView
     */
    public function __construct(
        OrderIndex $orderIndex,
        OrderInjectable $order,
        SalesOrderView $salesOrderView
    ) {
        $this->orderIndex = $orderIndex;
        $this->order = $order;
        $this->salesOrderView = $salesOrderView;
    }

    /**
     * Run step flow
     *
     * @return void
     */
    public function run()
    {
        $this->orderIndex->open();
        $this->orderIndex->getSalesOrderGrid()->searchAndOpen(['id' => $this->order->getId()]);
        $this->salesOrderView->getPageActions()->cancel();
    }
}
