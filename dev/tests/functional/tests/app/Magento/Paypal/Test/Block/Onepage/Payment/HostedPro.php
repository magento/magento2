<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Paypal\Test\Block\Onepage\Payment;

use Magento\Checkout\Test\Block\Onepage\Payment\Method;
use Magento\Mtf\Client\Locator;
use Magento\Mtf\Fixture\FixtureInterface;
use Magento\Mtf\ObjectManager;
use Magento\Paypal\Test\Block\Form\HostedPro\Cc;

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
    private $payNowButton = '#btn_pay_cc';

    /**
     * PayPal iframe selector.
     *
     * @var string
     */
    private $paypalIframe = '.paypal.iframe';

    /**
     * Credit card form selector.
     *
     * @var string
     */
    private $creditCardForm = '#formCreditCard';


    /**
     * Fill credit card data in PayPal iframe form.
     *
     * @param FixtureInterface $creditCard
     * @return void
     */
    public function fillPaymentData(FixtureInterface $creditCard)
    {
        $iframeLocator = ObjectManager::getInstance()->create(
            Locator::class,
            ['value' => $this->paypalIframe]
        );
        $this->browser->switchToFrame($iframeLocator);
        /** @var \Magento\Payment\Test\Block\Form\Cc $formBlock */
        $formBlock = $this->blockFactory->create(
            Cc::class,
            ['element' => $this->_rootElement->find($this->creditCardForm)]
        );
        $iframeRootElement = $this->browser->find('body');
        $formBlock->fill($creditCard, $iframeRootElement);
        $iframeRootElement->find($this->payNowButton)->click();
        $this->browser->switchToFrame();
    }
}
