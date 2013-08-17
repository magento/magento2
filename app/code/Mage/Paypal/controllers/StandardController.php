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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Paypal Standard Checkout Controller
 *
 * @category   Mage
 * @package    Mage_Paypal
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Paypal_StandardController extends Mage_Core_Controller_Front_Action
{
    /**
     * Order instance
     */
    protected $_order;

    /**
     *  Get order
     *
     *  @return	Mage_Sales_Model_Order
     */
    public function getOrder()
    {
        if ($this->_order == null) {
        }
        return $this->_order;
    }

    /**
     * Send expire header to ajax response
     */
    protected function _expireAjax()
    {
        if (!$this->_objectManager->get('Mage_Checkout_Model_Session')->getQuote()->hasItems()) {
            $this->getResponse()->setHeader('HTTP/1.1','403 Session Expired');
            exit;
        }
    }

    /**
     * Get singleton with paypal strandard order transaction information
     *
     * @return Mage_Paypal_Model_Standard
     */
    public function getStandard()
    {
        return $this->_objectManager->get('Mage_Paypal_Model_Standard');
    }

    /**
     * When a customer chooses Paypal on Checkout/Payment page
     */
    public function redirectAction()
    {
        $session = $this->_objectManager->get('Mage_Checkout_Model_Session');
        $session->setPaypalStandardQuoteId($session->getQuoteId());
        $this->loadLayout(false)->renderLayout();
        $session->unsQuoteId();
        $session->unsRedirectUrl();
    }

    /**
     * When a customer cancel payment from paypal.
     */
    public function cancelAction()
    {
        $session = $this->_objectManager->get('Mage_Checkout_Model_Session');
        $session->setQuoteId($session->getPaypalStandardQuoteId(true));

        if ($session->getLastRealOrderId()) {
            $order = $this->_objectManager->create('Mage_Sales_Model_Order')
                ->loadByIncrementId($session->getLastRealOrderId());
            if ($order->getId()) {
                $this->_objectManager->get('Mage_Core_Model_Event_Manager')->dispatch(
                    'paypal_payment_cancel',
                    array(
                        'order' => $order,
                        'quote' => $session->getQuote()
                ));
                $order->cancel()->save();
            }
            $this->_objectManager->get('Mage_Paypal_Helper_Checkout')->restoreQuote();
        }
        $this->_redirect('checkout/cart');
    }

    /**
     * when paypal returns
     * The order information at this point is in POST
     * variables.  However, you don't want to "process" the order until you
     * get validation from the IPN.
     */
    public function successAction()
    {
        $session = $this->_objectManager->get('Mage_Checkout_Model_Session');
        $session->setQuoteId($session->getPaypalStandardQuoteId(true));
        $session->getQuote()->setIsActive(false)->save();
        $this->_redirect('checkout/onepage/success', array('_secure'=>true));
    }
}
