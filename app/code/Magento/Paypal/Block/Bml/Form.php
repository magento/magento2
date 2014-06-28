<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
        )->setPaymentAcceptanceMarkSrc('https://www.paypalobjects.com/en_US/i/logo/logo_BMLPP_90x34.gif')
            ->setPaymentWhatIs(__('See terms'));

        $this->_initializeRedirectTemplateWithMark($mark);
    }
}
