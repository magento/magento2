<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\TestStep;

use Magento\Sales\Test\Page\Adminhtml\OrderCreateIndex;
use Magento\Mtf\TestStep\TestStepInterface;
use Magento\Payment\Test\Fixture\CreditCard;

/**
 * Fill Payment Data Step.
 */
class SelectPaymentMethodForOrderStep implements TestStepInterface
{
    /**
     * Sales order create index page.
     *
     * @var OrderCreateIndex
     */
    protected $orderCreateIndex;

    /**
     * Payment information.
     *
     * @var array
     */
    protected $payment;

    /**
     * Credit card information.
     *
     * @var CreditCard
     */
    private $creditCard;

    /**
     * @param OrderCreateIndex $orderCreateIndex
     * @param array $payment
     * @param CreditCard|null $creditCard
     */
    public function __construct(
        OrderCreateIndex $orderCreateIndex,
        array $payment,
        CreditCard $creditCard = null
    ) {
        $this->orderCreateIndex = $orderCreateIndex;
        $this->payment = $payment;
        $this->creditCard = $creditCard;
    }

    /**
     * Fill Payment data.
     *
     * @return void
     */
    public function run()
    {
        $this->orderCreateIndex->getCreateBlock()->selectPaymentMethod($this->payment, $this->creditCard);
    }
}
