<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Vault\Test\TestStep;

use Magento\Mtf\TestStep\TestStepInterface;
use Magento\Sales\Test\Page\Adminhtml\OrderCreateIndex;

/**
 * Class UseVaultPaymentTokenStep
 */
class UseVaultPaymentTokenStep implements TestStepInterface
{
    /**
     * @var OrderCreateIndex
     */
    private $orderCreatePage;
    
    /**
     * @var array
     */
    private $payment;

    /**
     * @param OrderCreateIndex $orderCreateIndex
     * @param array $payment
     */
    public function __construct(OrderCreateIndex $orderCreateIndex, array $payment)
    {
        $this->orderCreatePage = $orderCreateIndex;
        $this->payment = $payment;
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        $block = $this->orderCreatePage->getCreateBlock();
        $this->payment['method'] .= '_cc_vault';
        $block->selectPaymentMethod($this->payment);
        $block->selectVaultToken('token_switcher_' . $this->payment['method']);
    }
}
