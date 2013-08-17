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
 * Payflow Checkout Controller
 *
 * @category   Mage
 * @package    Mage_Paypal
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Paypal_PayflowController extends Mage_Core_Controller_Front_Action
{
    /**
     * When a customer cancel payment from payflow gateway.
     */
    public function cancelPaymentAction()
    {
        $this->loadLayout(false);
        $gotoSection = $this->_cancelPayment();
        $redirectBlock = $this->getLayout()->getBlock('payflow.link.iframe');
        $redirectBlock->setGotoSection($gotoSection);
        $this->renderLayout();
    }

    /**
     * When a customer return to website from payflow gateway.
     */
    public function returnUrlAction()
    {
        $this->loadLayout(false);
        $redirectBlock = $this->getLayout()->getBlock('payflow.link.iframe');

        $session = $this->_objectManager->get('Mage_Checkout_Model_Session');
        if ($session->getLastRealOrderId()) {
            $order = $this->_objectManager->create('Mage_Sales_Model_Order')->loadByIncrementId($session->getLastRealOrderId());

            if ($order && $order->getIncrementId() == $session->getLastRealOrderId()) {
                $allowedOrderStates = array(
                    Mage_Sales_Model_Order::STATE_PROCESSING,
                    Mage_Sales_Model_Order::STATE_COMPLETE
                );
                if (in_array($order->getState(), $allowedOrderStates)) {
                    $session->unsLastRealOrderId();
                    $redirectBlock->setGotoSuccessPage(true);
                } else {
                    $gotoSection = $this->_cancelPayment(strval($this->getRequest()->getParam('RESPMSG')));
                    $redirectBlock->setGotoSection($gotoSection);
                    $redirectBlock->setErrorMsg($this->__('Your payment has been declined. Please try again.'));
                }
            }
        }

        $this->renderLayout();
    }

    /**
     * Submit transaction to Payflow getaway into iframe
     */
    public function formAction()
    {
        $this->loadLayout(false)->renderLayout();
    }

    /**
     * Get response from PayPal by silent post method
     */
    public function silentPostAction()
    {
        $data = $this->getRequest()->getPost();
        if (isset($data['INVNUM'])) {
            /** @var $paymentModel Mage_Paypal_Model_Payflowlink */
            $paymentModel = Mage::getModel('Mage_Paypal_Model_Payflowlink');
            try {
                $paymentModel->process($data);
            } catch (Exception $e) {
                Mage::logException($e);
            }
        }
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
        $helper = $this->_objectManager->get('Mage_Paypal_Helper_Checkout');
        $helper->cancelCurrentOrder($errorMsg);
        if ($helper->restoreQuote()) {
            //Redirect to payment step
            $gotoSection = 'payment';
        }

        return $gotoSection;
    }
}
