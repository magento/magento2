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
 * @category    Mage
 * @package     Mage_XmlConnect
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Abstract Pbridge Payment method xml renderer
 *
 * @category    Mage
 * @package     Mage_XmlConnect
 * @author      Magento Core Team <core@magentocommerce.com>
 */
abstract class Mage_XmlConnect_Block_Checkout_Payment_Method_Pbridge_Abstract
    extends Enterprise_Pbridge_Block_Payment_Form_Abstract
{
    /**
     * Retrieve payment method model
     *
     * @return Mage_Payment_Model_Method_Abstract
     */
    public function getMethod()
    {
        $method = $this->getData('method');
        if (!$method) {
            $method = Mage::getModel('Enterprise_Pbridge_Model_Payment_Method_' . $this->_model);
            $this->setData('method', $method);
        }
        return $method;
    }

    /**
     * Return redirect url for Payment Bridge application
     *
     * @return string
     */
    public function getRedirectUrl()
    {
        return $this->getUrl('xmlconnect/pbridge/result', array('_current' => true, '_secure' => true));
    }

    /**
     * Add payment method through Pbridge iframe XML object
     *
     * @param Mage_XmlConnect_Model_Simplexml_Element $paymentItemXmlObj
     * @return Mage_XmlConnect_Model_Simplexml_Element
     */
    public function addPaymentFormToXmlObj(Mage_XmlConnect_Model_Simplexml_Element $paymentItemXmlObj)
    {
        $paymentItemXmlObj->addAttribute('is_pbridge', 1);
        $paymentItemXmlObj->addChild('pb_iframe', $paymentItemXmlObj->xmlentities($this->createIframe()));
        return $paymentItemXmlObj;
    }

    /**
     * Create html page with iframe for devices
     *
     * @return string html
     */
    protected function createIframe()
    {
        $code = $this->getMethodCode();
        $body = <<<EOT
<div id="payment_form_{$code}" style="margin:0 auto; max-width:500px;">
    {$this->getIframeBlock()->toHtml()}
</div>
EOT;
        return $this->helper('Mage_XmlConnect_Helper_Data')->htmlize($body);
    }
}
