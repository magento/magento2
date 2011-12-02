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
 * @package     Mage_Paypal
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * PayPal Standard payment "form"
 */
class Mage_Paypal_Block_Express_Form extends Mage_Paypal_Block_Standard_Form
{
    /**
     * Payment method code
     * @var string
     */
    protected $_methodCode = Mage_Paypal_Model_Config::METHOD_WPP_EXPRESS;

    /**
     * Set template and redirect message
     */
    protected function _construct()
    {
        $result = parent::_construct();
        $this->setRedirectMessage(Mage::helper('Mage_Paypal_Helper_Data')->__('You will be redirected to the PayPal website.'));
        return $result;
    }

    /**
     * Set data to block
     *
     * @return Mage_Core_Block_Abstract
     */
    protected function _beforeToHtml()
    {
        $customerId = Mage::getSingleton('Mage_Customer_Model_Session')->getCustomerId();
        if (Mage::helper('Mage_Paypal_Helper_Data')->shouldAskToCreateBillingAgreement($this->_config, $customerId)
             && $this->canCreateBillingAgreement()) {
            $this->setCreateBACode(Mage_Paypal_Model_Express_Checkout::PAYMENT_INFO_TRANSPORT_BILLING_AGREEMENT);
        }
        return parent::_beforeToHtml();
    }
}
