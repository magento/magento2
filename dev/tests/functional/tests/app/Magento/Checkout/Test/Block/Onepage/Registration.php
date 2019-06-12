<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Test\Block\Onepage;

use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;

/**
 * One page checkout registration block.
 */
class Registration extends Block
{
    /**
     * 'Create an Account' button.
     *
     * @var string
     */
<<<<<<< HEAD
    protected $createAccountButton = 'input[data-bind*="Create an Account"]';
=======
    protected $createAccountButton = '[data-bind*="i18n: \'Create an Account\'"]';
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc

    /**
     * Click 'Create an Account' button and wait until button will be not visible.
     *
     * @return void
     */
    public function createAccount()
    {
        $this->_rootElement->find($this->createAccountButton, Locator::SELECTOR_CSS)->click();
        $this->waitForElementNotVisible($this->createAccountButton, Locator::SELECTOR_CSS);
    }
}
