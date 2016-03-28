<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\TestStep;

use Magento\Sales\Test\Page\Adminhtml\OrderIndex;
use Magento\Mtf\TestStep\TestStepInterface;

/**
 * Class CreateNewOrderStep
 * Create new order
 */
class CreateNewOrderStep implements TestStepInterface
{
    /**
     * Sales order index page
     *
     * @var OrderIndex
     */
    protected $orderIndex;

    /**
     * @constructor
     * @param OrderIndex $orderIndex
     */
    public function __construct(OrderIndex $orderIndex)
    {
        $this->orderIndex = $orderIndex;
    }

    /**
     * Create new order
     *
     * @return void
     */
    public function run()
    {
        $this->orderIndex->getGridPageActions()->addNew();
    }
}
