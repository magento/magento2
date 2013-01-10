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
 * @category    Phoenix
 * @package     Phoenix_Moneybookers
 * @copyright   Copyright (c) 2013 Phoenix Medien GmbH & Co. KG (http://www.phoenix-medien.de)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Moneybookers notification processor model
 */
class Phoenix_Moneybookers_Model_Event
{
    const MONEYBOOKERS_STATUS_FAIL = -2;
    const MONEYBOOKERS_STATUS_CANCEL = -1;
    const MONEYBOOKERS_STATUS_PENDING = 0;
    const MONEYBOOKERS_STATUS_SUCCESS = 2;

    /*
     * @param Mage_Sales_Model_Order
     */
    protected $_order = null;

    /**
     * Event request data
     * @var array
     */
    protected $_eventData = array();

    /**
     * Enent request data setter
     * @param array $data
     * @return Phoenix_Moneybookers_Model_Event
     */
    public function setEventData(array $data)
    {
        $this->_eventData = $data;
        return $this;
    }

    /**
     * Event request data getter
     * @param string $key
     * @return array|string
     */
    public function getEventData($key = null)
    {
        if (null === $key) {
            return $this->_eventData;
        }
        return isset($this->_eventData[$key]) ? $this->_eventData[$key] : null;
    }

    /**
     * Get singleton of Checkout Session Model
     *
     * @return Mage_Checkout_Model_Session
     */
    protected function _getCheckout()
    {
        return Mage::getSingleton('Mage_Checkout_Model_Session');
    }

    /**
     * Process status notification from Monebookers server
     *
     * @return String
     */
    public function processStatusEvent()
    {
        try {
            $params = $this->_validateEventData();
            $msg = '';
            switch($params['status']) {
                case self::MONEYBOOKERS_STATUS_FAIL: //fail
                    $msg = Mage::helper('Phoenix_Moneybookers_Helper_Data')->__('Payment failed.');
                    $this->_processCancel($msg);
                    break;
                case self::MONEYBOOKERS_STATUS_CANCEL: //cancel
                    $msg = Mage::helper('Phoenix_Moneybookers_Helper_Data')->__('Payment was canceled.');
                    $this->_processCancel($msg);
                    break;
                case self::MONEYBOOKERS_STATUS_PENDING: //pending
                    $msg = Mage::helper('Phoenix_Moneybookers_Helper_Data')->__('Pending bank transfer created.');
                    $this->_processSale($params['status'], $msg);
                    break;
                case self::MONEYBOOKERS_STATUS_SUCCESS: //ok
                    $msg = Mage::helper('Phoenix_Moneybookers_Helper_Data')->__('The amount has been authorized and captured by Moneybookers.');
                    $this->_processSale($params['status'], $msg);
                    break;
            }
            return $msg;
        } catch (Mage_Core_Exception $e) {
            return $e->getMessage();
        } catch(Exception $e) {
            Mage::logException($e);
        }
        return;
    }

    /**
     * Process cancelation
     */
    public function cancelEvent() {
        try {
            $this->_validateEventData(false);
            $this->_processCancel('Payment was canceled.');
            return Mage::helper('Phoenix_Moneybookers_Helper_Data')->__('The order has been canceled.');
        } catch (Mage_Core_Exception $e) {
            return $e->getMessage();
        } catch(Exception $e) {
            Mage::logException($e);
        }
        return '';
    }

    /**
     * Validate request and return QuoteId
     * Can throw Mage_Core_Exception and Exception
     *
     * @return int
     */
    public function successEvent(){
        $this->_validateEventData(false);
        return $this->_order->getQuoteId();
    }

    /**
     * Processed order cancelation
     * @param string $msg Order history message
     */
    protected function _processCancel($msg)
    {
        $this->_order->cancel();
        $this->_order->addStatusToHistory(Mage_Sales_Model_Order::STATE_CANCELED, $msg);
        $this->_order->save();
    }

    /**
     * Processes payment confirmation, creates invoice if necessary, updates order status,
     * sends order confirmation to customer
     * @param string $msg Order history message
     */
    protected function _processSale($status, $msg)
    {
        switch ($status) {
            case self::MONEYBOOKERS_STATUS_SUCCESS:
                $this->_createInvoice();
                $this->_order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, true, $msg);
                // save transaction ID
                $this->_order->getPayment()->setLastTransId($this->getEventData('mb_transaction_id'));
                // send new order email
                $this->_order->sendNewOrderEmail();
                $this->_order->setEmailSent(true);
                break;
            case self::MONEYBOOKERS_STATUS_PENDING:
                $this->_order->setState(Mage_Sales_Model_Order::STATE_PENDING_PAYMENT, true, $msg);
                // save transaction ID
                $this->_order->getPayment()->setLastTransId($this->getEventData('mb_transaction_id'));
                break;
        }
        $this->_order->save();
    }

    /**
     * Builds invoice for order
     */
    protected function _createInvoice()
    {
        if (!$this->_order->canInvoice()) {
            return;
        }
        $invoice = $this->_order->prepareInvoice();
        $invoice->register()->capture();
        $this->_order->addRelatedObject($invoice);
    }

    /**
     * Checking returned parameters
     * Thorws Mage_Core_Exception if error
     * @param bool $fullCheck Whether to make additional validations such as payment status, transaction signature etc.
     *
     * @return array  $params request params
     */
    protected function _validateEventData($fullCheck = true)
    {
        // get request variables
        $params = $this->_eventData;
        if (empty($params)) {
            Mage::throwException('Request does not contain any elements.');
        }

        // check order ID
        if (empty($params['transaction_id'])
            || ($fullCheck == false && $this->_getCheckout()->getMoneybookersRealOrderId() != $params['transaction_id'])
        ) {
            Mage::throwException('Missing or invalid order ID.');
        }
        // load order for further validation
        $this->_order = Mage::getModel('Mage_Sales_Model_Order')->loadByIncrementId($params['transaction_id']);
        if (!$this->_order->getId()) {
            Mage::throwException('Order not found.');
        }

        if (0 !== strpos($this->_order->getPayment()->getMethodInstance()->getCode(), 'moneybookers_')) {
            Mage::throwException('Unknown payment method.');
        }

        // make additional validation
        if ($fullCheck) {
            // check payment status
            if (empty($params['status'])) {
                Mage::throwException('Unknown payment status.');
            }

            // check transaction signature
            if (empty($params['md5sig'])) {
                Mage::throwException('Invalid transaction signature.');
            }

            $checkParams = array('merchant_id', 'transaction_id', 'secret', 'mb_amount', 'mb_currency', 'status');
            $md5String = '';
            foreach ($checkParams as $key) {
                if ($key == 'merchant_id') {
                    $md5String .= Mage::getStoreConfig(Phoenix_Moneybookers_Helper_Data::XML_PATH_CUSTOMER_ID,
                        $this->_order->getStoreId()
                    );
                } elseif ($key == 'secret') {
                    $secretKey = Mage::getStoreConfig(
                        Phoenix_Moneybookers_Helper_Data::XML_PATH_SECRET_KEY,
                        $this->_order->getStoreId()
                    );

                    if (empty($secretKey)) {
                        Mage::throwException('Secret key is empty.');
                    }

                    $md5String .= strtoupper(md5($secretKey));
                } elseif (isset($params[$key])) {
                    $md5String .= $params[$key];
                }
            }
            $md5String = strtoupper(md5($md5String));

            if ($md5String != $params['md5sig']) {
                Mage::throwException('Hash is not valid.');
            }

            // check transaction amount if currency matches
            if ($this->_order->getOrderCurrencyCode() == $params['mb_currency']) {
                if (round($this->_order->getGrandTotal(), 2) != $params['mb_amount']) {
                    Mage::throwException('Transaction amount does not match.');
                }
            }
        }
        return $params;
    }
}
