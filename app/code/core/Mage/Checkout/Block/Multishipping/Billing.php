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
 * @package     Mage_Checkout
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Multishipping billing information
 *
 * @category   Mage
 * @package    Mage_Checkout
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Checkout_Block_Multishipping_Billing extends Mage_Payment_Block_Form_Container
{
    /**
     * Prepare children blocks
     */
    protected function _prepareLayout()
    {
        if ($headBlock = $this->getLayout()->getBlock('head')) {
            $headBlock->setTitle(
                Mage::helper('Mage_Checkout_Helper_Data')->__('Billing Information - %s', $headBlock->getDefaultTitle())
            );
        }

        return parent::_prepareLayout();
    }

    /**
     * Check and prepare payment method model
     *
     * @return bool
     */
    protected function _canUseMethod($method)
    {
        if (!$method->canUseForMultishipping()) {
            return false;
        }
        return parent::_canUseMethod($method);
    }

    /**
     * Retrieve code of current payment method
     *
     * @return mixed
     */
    public function getSelectedMethodCode()
    {
        if ($method = $this->getQuote()->getPayment()->getMethod()) {
            return $method;
        }
        return false;
    }

    /**
     * Retrieve billing address
     *
     * @return Mage_Sales_Model_Quote_Address
     */
    public function getAddress()
    {
        $address = $this->getData('address');
        if (is_null($address)) {
            $address = Mage::getSingleton('Mage_Checkout_Model_Type_Multishipping')->getQuote()->getBillingAddress();
            $this->setData('address', $address);
        }
        return $address;
    }

    /**
     * Retrieve quote model object
     *
     * @return Mage_Sales_Model_Quote
     */
    public function getQuote()
    {
        return Mage::getSingleton('Mage_Checkout_Model_Session')->getQuote();
    }

    /**
     * Getter
     *
     * @return float
     */
    public function getQuoteBaseGrandTotal()
    {
        return (float)$this->getQuote()->getBaseGrandTotal();
    }

    /**
     * Retrieve url for select billing address
     *
     * @return string
     */
    public function getSelectAddressUrl()
    {
        return $this->getUrl('*/multishipping_address/selectBilling');
    }

    /**
     * Retrieve data post destination url
     *
     * @return string
     */
    public function getPostActionUrl()
    {
        //return $this->getUrl('*/*/billingPost');
        return $this->getUrl('*/*/overview');
    }

    /**
     * Retrieve back url
     *
     * @return string
     */
    public function getBackUrl()
    {
        return $this->getUrl('*/*/backtoshipping');
    }
}
