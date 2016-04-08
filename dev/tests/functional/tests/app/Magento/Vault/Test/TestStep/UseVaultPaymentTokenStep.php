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
     * @param OrderCreateIndex $orderCreateIndex
     */
    public function __construct(OrderCreateIndex $orderCreateIndex)
    {
        $this->orderCreatePage = $orderCreateIndex;
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        $block = $this->orderCreatePage->getCreateBlock();
        $block->selectPaymentMethod(['method' => 'vault']);
        $block->selectVaultToken('token_switcher_');
    }
}
