<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Test\Block\Paypal;

use Magento\Mtf\Block\Block;

/**
 * Class PopupWindow
 */
class PopupWindow extends Block
{
    /**
     * @var string
     */
    private $selector = '#login-preview';

    /**
     * @var string
     */
    private $submitButton = '#return_url';

    /**
     * @var string
     */
    private $loader = '.loader';

    /**
     * Waits for PayPal popup loading
     *
     * @return void
     */
    public function waitForFormLoaded()
    {
        $this->waitForElementVisible($this->selector);
    }

    /**
     * Process PayPal auth flow
     *
     * @param null|string $parentWindow
     * 
     */
    public function process($parentWindow = null)
    {
        $this->browser->selectWindow();
        $this->waitForFormLoaded();
        $this->browser->find($this->submitButton)->click();
        $this->browser->selectWindow($parentWindow);
        $this->waitForElementNotVisible($this->loader);
    }
}
