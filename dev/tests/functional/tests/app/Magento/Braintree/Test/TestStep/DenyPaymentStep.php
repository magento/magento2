<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Test\TestStep;

use Magento\Sales\Test\Fixture\OrderInjectable;
use Magento\Sales\Test\Page\Adminhtml\OrderIndex;
use Magento\Sales\Test\Page\Adminhtml\SalesOrderView;
use Magento\Mtf\TestStep\TestStepInterface;

/**
 * Class DenyPaymentStep
 */
class DenyPaymentStep implements TestStepInterface
{
    /**
     * @var OrderIndex
     */
    private $orderIndex;

    /**
     * @var SalesOrderView
     */
    private $salesOrderView;

    /**
     * @var OrderInjectable
     */
    private $order;

    public function __construct(OrderIndex $orderIndex, SalesOrderView $salesOrderView, OrderInjectable $order)
    {
        $this->orderIndex = $orderIndex;
        $this->salesOrderView = $salesOrderView;
        $this->order = $order;
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        $this->orderIndex->open();
        $this->orderIndex->getSalesOrderGrid()->searchAndOpen(['id' => $this->order->getId()]);
        $this->salesOrderView->getPageActions()->deny();
    }
}
