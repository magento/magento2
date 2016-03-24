<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\TestStep;

use Magento\Mtf\Fixture\FixtureFactory;
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
     * @constructor
     * @param OrderCreateIndex $orderCreateIndex
     * @param array $payment
     * @param FixtureFactory $fixtureFactory
     * @param string $creditCardClass
     * @param array|CreditCard|null $creditCard
     */
    public function __construct(
        OrderCreateIndex $orderCreateIndex,
        array $payment,
        FixtureFactory $fixtureFactory,
        $creditCardClass = 'credit_card',
        array $creditCard = null
    ) {
        $this->orderCreateIndex = $orderCreateIndex;
        $this->payment = $payment;
        if (isset($creditCard['dataset'])) {
            $this->creditCard = $fixtureFactory->createByCode($creditCardClass, ['dataset' => $creditCard['dataset']]);
        }
    }

    /**
     * Fill Payment data
     *
     * @return void
     */
    public function run()
    {
        $this->orderCreateIndex->getCreateBlock()->selectPaymentMethod($this->payment, $this->creditCard);
    }
}
