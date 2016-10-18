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
    private $vault;

    /**
     * @param OrderCreateIndex $orderCreateIndex
     * @param array $vault
     */
    public function __construct(OrderCreateIndex $orderCreateIndex, array $vault)
    {
        $this->orderCreatePage = $orderCreateIndex;
        $this->vault = $vault;
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        $block = $this->orderCreatePage->getCreateBlock();
        $block->selectPaymentMethod($this->vault);
        $block->selectVaultToken('token_switcher_' . $this->vault['method']);
    }
}
