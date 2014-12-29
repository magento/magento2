<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Sales\Test\TestStep;

use Magento\Sales\Test\Page\Adminhtml\OrderIndex;
use Mtf\TestStep\TestStepInterface;

/**
 * Class OpenSalesOrdersStep
 * Open Sales Orders
 */
class OpenSalesOrdersStep implements TestStepInterface
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
     * Open Sales order
     *
     * @return void
     */
    public function run()
    {
        $this->orderIndex->open();
    }
}
