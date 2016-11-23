<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Paypal\Test\Block\Onepage\Payment;

use Magento\Checkout\Test\Block\Onepage\Payment\Method;
use Magento\Mtf\Client\Locator;
use Magento\Mtf\Fixture\FixtureInterface;

/**
 * Hosted Pro credit card block.
 */
class HostedPro extends Method
{
    /**
     * 'Pay Now' button selector.
     *
     * @var string
     */
    protected $payNowButton = '#btn_pay_cc';


    public function fillPaymentData(FixtureInterface $creditCard)
    {
        $this->browser->switchToFrame(new Locator('.paypal.iframe'));
        /** @var \Magento\Payment\Test\Block\Form\Cc $formBlock */
        $formBlock = $this->blockFactory->create(
            "\\Magento\\Paypal\\Test\\Block\\Form\\HostedPro\\Cc",
            ['element' => $this->_rootElement->find('#formCreditCard')]
        );
        $iframeRootElement = $this->browser->find('body');
        $formBlock->fill($creditCard, $iframeRootElement);
        $iframeRootElement->find($this->payNowButton)->click();
        $this->browser->switchToFrame();
    }
}
