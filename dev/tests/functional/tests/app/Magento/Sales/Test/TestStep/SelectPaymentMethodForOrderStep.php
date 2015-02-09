<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\TestStep;

use Magento\Sales\Test\Page\Adminhtml\OrderCreateIndex;
use Magento\Mtf\TestStep\TestStepInterface;

/**
 * Class SelectPaymentMethodForOrderStep
 * Fill Payment Data Step
 */
class SelectPaymentMethodForOrderStep implements TestStepInterface
{
    /**
     * Sales order create index page
     *
     * @var OrderCreateIndex
     */
    protected $orderCreateIndex;

    /**
     * Payment
     *
     * @var array
     */
    protected $payment;

    /**
     * @constructor
     * @param OrderCreateIndex $orderCreateIndex
     * @param array $payment
     */
    public function __construct(OrderCreateIndex $orderCreateIndex, array $payment)
    {
        $this->orderCreateIndex = $orderCreateIndex;
        $this->payment = $payment;
    }

    /**
     * Fill Payment data
     *
     * @return void
     */
    public function run()
    {
        $this->orderCreateIndex->getCreateBlock()->selectPaymentMethod($this->payment);
    }
}
