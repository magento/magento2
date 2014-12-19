<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Sales\Test\TestStep;

use Magento\Sales\Test\Page\Adminhtml\OrderCreateIndex;
use Mtf\TestStep\TestStepInterface;

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
