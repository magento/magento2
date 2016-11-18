<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Vault\Test\TestStep;

use Magento\Checkout\Test\Page\CheckoutOnepage;
use Magento\Mtf\TestStep\TestStepInterface;
use Magento\Vault\Test\Constraint\AssertSaveCreditCardOptionNotPresent;

/**
 * Check if customer cannot save credit card for later use if vault is disabled.
 */
class CheckSaveCreditCardOptionStep implements TestStepInterface
{
    /**
     * Onepage checkout page.
     *
     * @var CheckoutOnepage
     */
    private $checkoutOnepage;

    /**
     * Assert that 'Save for later use' checkbox is not present in credit card form.
     *
     * @var AssertSaveCreditCardOptionNotPresent
     */
    private $assertSaveCreditCardOptionNotPresent;

    /**
     * Payment method.
     *
     * @var array
     */
    private $payment;

    /**
     * If vault is enabled for payment method.
     *
     * @var null|bool
     */
    private $isVaultEnabled;

    /**
     * @param CheckoutOnepage $checkoutOnepage
     * @param AssertSaveCreditCardOptionNotPresent $assertSaveCreditCardOptionNotPresent
     * @param array $payment
     * @param null|bool $isVaultEnabled
     */
    public function __construct(
        CheckoutOnepage $checkoutOnepage,
        AssertSaveCreditCardOptionNotPresent $assertSaveCreditCardOptionNotPresent,
        array $payment,
        $isVaultEnabled = null
    ) {
        $this->checkoutOnepage = $checkoutOnepage;
        $this->assertSaveCreditCardOptionNotPresent = $assertSaveCreditCardOptionNotPresent;
        $this->payment = $payment;
        $this->isVaultEnabled = $isVaultEnabled;
    }

    /**
     * Run step that verifies if 'Save for later use' checkbox is not present in credit card form.
     *
     * @return void
     */
    public function run()
    {
        if ($this->isVaultEnabled === false) {
            $this->assertSaveCreditCardOptionNotPresent->processAssert(
                $this->checkoutOnepage,
                $this->payment['method']
            );
        }
    }
}
