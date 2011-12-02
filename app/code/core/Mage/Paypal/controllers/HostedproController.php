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
 * Hosted Pro Checkout Controller
 *
 * @category   Mage
 * @package    Mage_Paypal
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Paypal_HostedproController extends Mage_Core_Controller_Front_Action
{
    /**
     * When a customer return to website from gateway.
     */
    public function returnAction()
    {
        $session = $this->_getCheckout();
        //TODO: some actions with order
        if ($session->getLastRealOrderId()) {
            $this->_redirect('checkout/onepage/success');
        }
    }

    /**
     * When a customer cancel payment from gateway.
     */
    public function cancelAction()
    {
        $gotoSection = $this->_cancelPayment();
        $redirectBlock = $this->_getIframeBlock()
            ->setGotoSection($gotoSection)
            ->setTemplate('hss/redirect.phtml');
        //TODO: clarify return logic whether customer will be returned in iframe or in parent window
        $this->getResponse()->setBody($redirectBlock->toHtml());
    }

    /**
     * Cancel order, return quote to customer
     *
     * @param string $errorMsg
     * @return mixed
     */
    protected function _cancelPayment($errorMsg = '')
    {
        $gotoSection = false;
        $session = $this->_getCheckout();
        if ($session->getLastRealOrderId()) {
            $order = Mage::getModel('Mage_Sales_Model_Order')->loadByIncrementId($session->getLastRealOrderId());
            if ($order->getId()) {
                //Cancel order
                if ($order->getState() != Mage_Sales_Model_Order::STATE_CANCELED) {
                    $order->registerCancellation($errorMsg)->save();
                }
                $quote = Mage::getModel('Mage_Sales_Model_Quote')
                    ->load($order->getQuoteId());
                //Return quote
                if ($quote->getId()) {
                    $quote->setIsActive(1)
                        ->setReservedOrderId(NULL)
                        ->save();
                    $session->replaceQuote($quote);
                }
                //Unset data
                $session->unsLastRealOrderId();
                //Redirect to payment step
                $gotoSection = 'payment';
            }
        }

        return $gotoSection;
    }

    /**
     * Get frontend checkout session object
     *
     * @return Mage_Checkout_Model_Session
     */
    protected function _getCheckout()
    {
        return Mage::getSingleton('Mage_Checkout_Model_Session');
    }

    /**
     * Get iframe block
     *
     * @return Mage_Paypal_Block_Hosted_Pro_Iframe
     */
    protected function _getIframeBlock()
    {
        $this->loadLayout('paypal_hosted_pro_iframe');
        return $this->getLayout()
            ->getBlock('hosted.pro.iframe');
    }
}
