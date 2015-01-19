<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\TestStep;

use Magento\Sales\Test\Page\Adminhtml\OrderView;
use Mtf\TestStep\TestStepInterface;

/**
 * Class ReorderStep
 * Click reorder from order on backend
 */
class ReorderStep implements TestStepInterface
{
    /**
     * Order View Page
     *
     * @var OrderView
     */
    protected $orderView;

    /**
     * @construct
     * @param OrderView $orderView
     */
    public function __construct(OrderView $orderView)
    {
        $this->orderView = $orderView;
    }

    /**
     * Click reorder
     *
     * @return void
     */
    public function run()
    {
        $this->orderView->getPageActions()->reorder();
    }
}
