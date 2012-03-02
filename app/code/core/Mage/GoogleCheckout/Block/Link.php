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
 * @package     Mage_GoogleCheckout
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Google Checkout shortcut link
 *
 * @category   Mage
 * @package    Mage_GoogleCheckout
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_GoogleCheckout_Block_Link extends Mage_Core_Block_Template
{
    public function getImageStyle()
    {
        $s = Mage::getStoreConfig('google/checkout/checkout_image');
        if (!$s) {
            $s = '180/46/trans';
        }
        return explode('/', $s);
    }

    public function getImageUrl()
    {
        $url = 'https://checkout.google.com/buttons/checkout.gif';
        $url .= '?merchant_id='.Mage::getStoreConfig('google/checkout/merchant_id');
        $v = $this->getImageStyle();
        $url .= '&w='.$v[0].'&h='.$v[1].'&style='.$v[2];
        $url .= '&variant='.($this->getIsDisabled() ? 'disabled' : 'text');
        $url .= '&loc='.Mage::getStoreConfig('google/checkout/locale');
        return $url;
    }

    public function getCheckoutUrl()
    {
        return $this->getUrl('googlecheckout/redirect/checkout');
    }

    public function getImageWidth()
    {
         $v = $this->getImageStyle();
         return $v[0];
    }

    public function getImageHeight()
    {
         $v = $this->getImageStyle();
         return $v[1];
    }

    /**
     * Check whether method is available and render HTML
     * @return string
     */
    public function _toHtml()
    {
        $quote = Mage::getSingleton('Mage_Checkout_Model_Session')->getQuote();
        if (Mage::getModel('Mage_GoogleCheckout_Model_Payment')->isAvailable($quote) && $quote->validateMinimumAmount()) {
            Mage::dispatchEvent('googlecheckout_block_link_html_before', array('block' => $this));
            return parent::_toHtml();
        }
        return '';
    }

    public function getIsDisabled()
    {
        $quote = Mage::getSingleton('Mage_Checkout_Model_Session')->getQuote();
        /* @var $quote Mage_Sales_Model_Quote */
        foreach ($quote->getAllVisibleItems() as $item) {
            /* @var $item Mage_Sales_Model_Quote_Item */
            if (!$item->getProduct()->getEnableGooglecheckout()) {
                return true;
            }
        }
        return false;
    }
}
