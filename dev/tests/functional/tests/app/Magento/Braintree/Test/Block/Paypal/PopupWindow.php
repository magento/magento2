<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
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
     * @return void
     */
    public function process()
    {
        $this->browser->selectWindow();
        $this->waitForFormLoaded();
        $this->browser->find($this->submitButton)->click();
        /**
         * This try/catch is intended as workaround in case if popup window is closed instantly and #login-preview
         * selector cannot be found.
         */
        try {
            $this->waitForElementNotVisible($this->selector);
        } catch (\PHPUnit_Extensions_Selenium2TestCase_WebDriverException $e) {
        }
        $this->browser->selectWindow();
        $this->waitForElementNotVisible($this->loader);
    }
}
