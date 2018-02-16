<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Paypal\Test\Block\Form\PayflowAdvanced;

use Magento\Mtf\Client\Locator;
use Magento\Payment\Test\Block\Form\Cc;

/**
 * Card Verification frame block.
 */
class CcLink extends Cc
{
    /**
     * 'Pay Now' button.
     *
     * @var string
     */
    protected $continue = '#btn_pay_cc';

    /**
     * Payflow Link iFrame locator.
     *
     * @var string
     */
    protected $payflowLinkFrame = "#payflow-link-iframe";

    /**
     * Initialize block. Switch to frame.
     *
     * @return void
     */
    protected function init()
    {
        parent::init();
        $this->browser->switchToFrame(new Locator($this->payflowLinkFrame));
    }

    /**
     * Press "Continue" button.
     *
     * @return void
     */
    public function pressContinue()
    {
        $this->_rootElement->find($this->continue)->click();
    }
}
