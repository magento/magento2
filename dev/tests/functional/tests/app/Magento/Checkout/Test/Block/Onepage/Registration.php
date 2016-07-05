<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
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
    protected $createAccountButton = '//form[contains(@data-bind, "createAccount")]/input';

    /**
     * Click 'Create an Account' button.
     *
     * @return void
     */
    public function clickCreateAccountButton()
    {
        $this->_rootElement->find($this->createAccountButton, Locator::SELECTOR_XPATH)->click();
    }
}
