<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
