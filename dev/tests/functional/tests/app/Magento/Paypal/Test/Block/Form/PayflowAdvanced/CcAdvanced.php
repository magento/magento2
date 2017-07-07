<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Paypal\Test\Block\Form\PayflowAdvanced;

use Magento\Mtf\Client\Locator;
use Magento\Payment\Test\Block\Form\Cc;

/**
 * Card Verification frame block.
 */
class CcAdvanced extends Cc
{
    /**
     * 'Pay Now' button.
     *
     * @var string
     */
    protected $continue = '#btn_pay_cc';

    /**
     * Payflow Advanced iFrame locator.
     *
     * @var string
     */
    protected $payflowAdvancedFrame = "#payflow-advanced-iframe";

    /**
     * Initialize block. Switch to frame.
     *
     * @return void
     */
    protected function init()
    {
        parent::init();
        $this->browser->switchToFrame(new Locator($this->payflowAdvancedFrame));
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
