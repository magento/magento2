<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\TestStep;

use Magento\Sales\Test\Fixture\OrderInjectable;
use Magento\Sales\Test\Page\Adminhtml\OrderIndex;
use Magento\Mtf\TestStep\TestStepInterface;

/**
 * Open order step.
 */
class OpenOrderStep implements TestStepInterface
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
     * @constructor
     * @param OrderInjectable $order
     * @param OrderIndex $orderIndex
     */
    public function __construct(OrderInjectable $order, OrderIndex $orderIndex)
    {
        $this->orderIndex = $orderIndex;
        $this->order = $order;
    }

    /**
     * Open order.
     *
     * @return void
     */
    public function run()
    {
        $this->orderIndex->open();
        $this->orderIndex->getSalesOrderGrid()->searchAndOpen(['id' => $this->order->getId()]);
    }
}
