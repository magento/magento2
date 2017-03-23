<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Multishipping\Test\TestStep;

use Magento\Multishipping\Test\Page\MultishippingCheckoutBilling;
use Magento\Mtf\TestStep\TestStepInterface;

/**
 * Fill customer payment method and proceed to next step.
 */
class SelectPaymentMethodStep implements TestStepInterface
{
    /**
     * Multishipping checkout billing information page.
     *
     * @var MultishippingCheckoutBilling
     */
    protected $billingInformation;

    /**
     * Payment method.
     *
     * @var array
     */
    protected $payment;

    /**
     * @param MultishippingCheckoutBilling $billingInformation
     * @param array $payment
     */
    public function __construct(MultishippingCheckoutBilling $billingInformation, array $payment)
    {
        $this->billingInformation = $billingInformation;
        $this->payment = $payment;
    }

    /**
     * Select payment method.
     *
     * @return void
     */
    public function run()
    {
        $this->billingInformation->getBillingBlock()->selectPaymentMethod($this->payment);
    }
}
