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
 * @category    Mage
 * @package     Mage_GoogleCheckout
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_GoogleCheckout_RedirectController extends Mage_Core_Controller_Front_Action
{
    /**
     *  Send request to Google Checkout and return Response Api
     *
     *  @return Mage_GoogleCheckout_Model_Api_Xml_Checkout
     */
    protected function _getApi ()
    {
        $session = Mage::getSingleton('Mage_Checkout_Model_Session');
        $api = Mage::getModel('Mage_GoogleCheckout_Model_Api');
        /* @var $quote Mage_Sales_Model_Quote */
        $quote = $session->getQuote();

        if (!$quote->hasItems()) {
            $this->getResponse()->setRedirect(Mage::getUrl('checkout/cart'));
            $api->setError(true);
        }

        $storeQuote = Mage::getModel('Mage_Sales_Model_Quote')->setStoreId(Mage::app()->getStore()->getId());
        $storeQuote->merge($quote);
        $storeQuote
            ->setItemsCount($quote->getItemsCount())
            ->setItemsQty($quote->getItemsQty())
            ->setChangedFlag(false);
        $storeQuote->save();

        $baseCurrency = $quote->getBaseCurrencyCode();
        $currency = Mage::app()->getStore($quote->getStoreId())->getBaseCurrency();


        /*
         * Set payment method to google checkout, so all price rules will work out this case
         * and will use right sales rules
         */
        if ($quote->isVirtual()) {
            $quote->getBillingAddress()->setPaymentMethod('googlecheckout');
        } else {
            $quote->getShippingAddress()->setPaymentMethod('googlecheckout');
        }

        $quote->collectTotals()->save();

        if (!$api->getError()) {
            $api = $api->setAnalyticsData($this->getRequest()->getPost('analyticsdata'))
                ->checkout($quote);

            $response = $api->getResponse();
            if ($api->getError()) {
                Mage::getSingleton('Mage_Checkout_Model_Session')->addError($api->getError());
            } else {
                $quote->setIsActive(false)->save();
                $session->replaceQuote($storeQuote);
                Mage::getModel('Mage_Checkout_Model_Cart')->init()->save();
                if (Mage::getStoreConfigFlag('google/checkout/hide_cart_contents')) {
                    $session->setGoogleCheckoutQuoteId($session->getQuoteId());
                    $session->setQuoteId(null);
                }
            }
        }
        return $api;
    }

    public function checkoutAction()
    {
        $session = Mage::getSingleton('Mage_Checkout_Model_Session');
        Mage::dispatchEvent('googlecheckout_checkout_before', array('quote' => $session->getQuote()));
        $api = $this->_getApi();

        if ($api->getError()) {
            $url = Mage::getUrl('checkout/cart');
        } else {
            $url = $api->getRedirectUrl();
        }
        $this->getResponse()->setRedirect($url);
    }

    /**
     * When a customer chooses Google Checkout on Checkout/Payment page
     *
     */
    public function redirectAction()
    {
        $api = $this->_getApi();

        if ($api->getError()) {
            $this->getResponse()->setRedirect(Mage::getUrl('checkout/cart'));
            return;
        } else {
            $url = $api->getRedirectUrl();
            $this->loadLayout();
            $this->getLayout()->getBlock('googlecheckout_redirect')->setRedirectUrl($url);
            $this->renderLayout();
        }
    }

    public function cartAction()
    {
        if (Mage::getStoreConfigFlag('google/checkout/hide_cart_contents')) {
            $session = Mage::getSingleton('Mage_Checkout_Model_Session');
            if ($session->getQuoteId()) {
                $session->getQuote()->delete();
            }
            $session->setQuoteId($session->getGoogleCheckoutQuoteId());
            $session->setGoogleCheckoutQuoteId(null);
        }

        $this->_redirect('checkout/cart');
    }

    public function continueAction()
    {
        $session = Mage::getSingleton('Mage_Checkout_Model_Session');

        if ($quoteId = $session->getGoogleCheckoutQuoteId()) {
            $quote = Mage::getModel('Mage_Sales_Model_Quote')->load($quoteId)
                ->setIsActive(false)->save();
        }
        $session->clear();

        if (Mage::getStoreConfigFlag('google/checkout/hide_cart_contents')) {
            $session->setGoogleCheckoutQuoteId(null);
        }

        $url = Mage::getStoreConfig('google/checkout/continue_shopping_url');
        if (empty($url)) {
            $this->_redirect('');
        } elseif (substr($url, 0, 4) === 'http') {
            $this->getResponse()->setRedirect($url);
        } else {
            $this->_redirect($url);
        }
    }

    /**
     * Redirect to login page
     *
     */
    public function redirectLogin()
    {
        $this->setFlag('', 'no-dispatch', true);
        $this->getResponse()->setRedirect(
            Mage::helper('Mage_Core_Helper_Url')->addRequestParam(
                Mage::helper('Mage_Customer_Helper_Data')->getLoginUrl(),
                array('context' => 'checkout')
            )
        );
    }

}
