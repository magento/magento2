<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Test\TestStep;

use Magento\Mtf\TestStep\TestStepInterface;
use Magento\Sales\Test\Page\Adminhtml\OrderCreateIndex;

/**
 * Selects Braintree PayPal Vault stored payment method
 */
class UsePayPalVaultTokenStep implements TestStepInterface
{
    /**
     * @var OrderCreateIndex
     */
    private $orderCreateIndex;

    /**
     * @var array
     */
    private $payment;

    /**
     * UsePayPalVaultToken constructor.
     * @param OrderCreateIndex $orderCreateIndex
     * @param array $payment
     */
    public function __construct(OrderCreateIndex $orderCreateIndex, array $payment)
    {
        $this->orderCreateIndex = $orderCreateIndex;
        $this->payment = $payment;
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        $block = $this->orderCreateIndex->getCreateBlock();
        $this->payment['method'] = 'braintree_paypal_vault';
        $block->selectPaymentMethod($this->payment);
        $block->selectVaultToken('token_switcher_' . $this->payment['method']);
    }
}
