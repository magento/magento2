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
 * @category    Magento
 * @package     Magento_GoogleCheckout
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * @category    Magento
 * @package     Magento_GoogleCheckout
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\GoogleCheckout\Controller;

class Redirect extends \Magento\App\Action\Action
{
    /**
     *  Send request to Google Checkout and return Response Api
     *
     *  @return \Magento\GoogleCheckout\Model\Api\Xml\Checkout
     */
    protected function _getApi ()
    {
        $session = $this->_objectManager->get('Magento\Checkout\Model\Session');
        $api = $this->_objectManager->create('Magento\GoogleCheckout\Model\Api');
        /* @var $quote \Magento\Sales\Model\Quote */
        $quote = $session->getQuote();

        if (!$quote->hasItems()) {
            $this->getResponse()->setRedirect(
                $this->_objectManager->create('Magento\UrlInterface')->getUrl('checkout/cart')
            );
            $api->setError(true);
        }

        $storeQuote = $this->_objectManager->create('Magento\Sales\Model\Quote')->setStoreId(
            $this->_objectManager->get('Magento\Core\Model\StoreManager')->getStore()->getId()
        );
        $storeQuote->merge($quote);
        $storeQuote
            ->setItemsCount($quote->getItemsCount())
            ->setItemsQty($quote->getItemsQty())
            ->setChangedFlag(false);
        $storeQuote->save();

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

            $api->getResponse();
            if ($api->getError()) {
                $this->messageManager->addError($api->getError());
            } else {
                $quote->setIsActive(false)->save();
                $session->replaceQuote($storeQuote);
                $this->_objectManager->create('Magento\Checkout\Model\Cart')->init()->save();
                if ($this->_objectManager->get('Magento\Core\Model\Store\Config')
                    ->getConfigFlag('google/checkout/hide_cart_contents')
                ) {
                    $session->setGoogleCheckoutQuoteId($session->getQuoteId());
                    $session->setQuoteId(null);
                }
            }
        }
        return $api;
    }

    public function checkoutAction()
    {
        $session = $this->_objectManager->get('Magento\Checkout\Model\Session');
        $this->_eventManager->dispatch('googlecheckout_checkout_before', array('quote' => $session->getQuote()));
        $api = $this->_getApi();

        if ($api->getError()) {
            $url = $this->_objectManager->create('Magento\UrlInterface')->getUrl('checkout/cart');
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
            $this->getResponse()->setRedirect(
                $this->_objectManager->create('Magento\UrlInterface')->getUrl('checkout/cart')
            );
            return;
        } else {
            $url = $api->getRedirectUrl();
            $this->_view->loadLayout();
            $this->_view->getLayout()->getBlock('googlecheckout_redirect')->setRedirectUrl($url);
            $this->_view->renderLayout();
        }
    }

    public function cartAction()
    {
        $hideCartContents = $this->_objectManager->get('Magento\Core\Model\Store\Config')
            ->getConfigFlag('google/checkout/hide_cart_contents');
        if ($hideCartContents) {
            $session = $this->_objectManager->get('Magento\Checkout\Model\Session');
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
        /** @var \Magento\Checkout\Model\Session $session */
        $session = $this->_objectManager->get('Magento\Checkout\Model\Session');

        if ($quoteId = $session->getGoogleCheckoutQuoteId()) {
            $quote = $this->_objectManager->create('Magento\Sales\Model\Quote')->load($quoteId)
                ->setIsActive(false)->save();
        }
        $session->clearQuote();

        $hideCartContents = $this->_objectManager->get('Magento\Core\Model\Store\Config')
            ->getConfigFlag('google/checkout/hide_cart_contents');
        if ($hideCartContents) {
            $session->setGoogleCheckoutQuoteId(null);
        }

        $url = $this->_objectManager->get('Magento\Core\Model\Store\Config')
            ->getConfig('google/checkout/continue_shopping_url');
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
     */
    public function redirectLogin()
    {
        $this->_actionFlag->set('', 'no-dispatch', true);
        $this->getResponse()->setRedirect(
            $this->_objectManager->get('Magento\Core\Helper\Url')->addRequestParam(
                $this->_objectManager->get('Magento\Customer\Helper\Data')->getLoginUrl(),
                array('context' => 'checkout')
            )
        );
    }
}
