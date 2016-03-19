<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Paypal\Block\Bml;

use Magento\Paypal\Block\Express;
use Magento\Paypal\Model\Config;

class Form extends Express\Form
{
    /**
     * Payment method code
     * @var string
     */
    protected $_methodCode = Config::METHOD_WPP_BML;

    /**
     * Set template and redirect message
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_config = $this->_paypalConfigFactory->create()->setMethod($this->getMethodCode());
        /** @var $mark \Magento\Framework\View\Element\Template */
        $mark = $this->_getMarkTemplate();
        $mark->setPaymentAcceptanceMarkHref(
            'https://www.securecheckout.billmelater.com/paycapture-content/'
            . 'fetch?hash=AU826TU8&content=/bmlweb/ppwpsiw.html'
        )->setPaymentAcceptanceMarkSrc(
            'https://www.paypalobjects.com/webstatic/en_US/i/buttons/ppc-acceptance-medium.png'
        )->setPaymentWhatIs(__('See terms'));

        $this->_initializeRedirectTemplateWithMark($mark);
    }
}
