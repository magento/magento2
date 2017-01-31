<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Vault\Test\TestStep;

use Magento\Checkout\Test\Page\CheckoutOnepage;
use Magento\Mtf\TestStep\TestStepInterface;

/**
 * Save credit card during checkout.
 */
class SaveCreditCardStep implements TestStepInterface
{
    /**
     * Onepage checkout page.
     *
     * @var CheckoutOnepage
     */
    protected $checkoutOnepage;

    /**
     * Payment information.
     *
     * @var array
     */
    protected $payment;

    /**
     * Determines whether credit card should be saved.
     *
     * @var string
     */
    protected $creditCardSave;

    /**
     * @constructor
     * @param CheckoutOnepage $checkoutOnepage
     * @param array $payment
     * @param string $creditCardSave;
     */
    public function __construct(
        CheckoutOnepage $checkoutOnepage,
        array $payment,
        $creditCardSave = 'No'
    ) {
        $this->checkoutOnepage = $checkoutOnepage;
        $this->payment = $payment;
        $this->creditCardSave = $creditCardSave;
    }

    /**
     * Run step that saves credit card.
     *
     * @return void
     */
    public function run()
    {
        $this->checkoutOnepage->getPaymentBlock()->getSelectedPaymentMethodBlock()->saveCreditCard(
            $this->payment['method'],
            $this->creditCardSave
        );
    }
}
