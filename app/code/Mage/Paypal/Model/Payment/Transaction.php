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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Payment transaction model
 * Tracks transaction history
 *
 * @method Mage_Paypal_Model_Resource_Payment_Transaction _getResource()
 * @method Mage_Paypal_Model_Resource_Payment_Transaction getResource()
 * @method string getTxnId()
 * @method string getCreatedAt()
 * @method Mage_Paypal_Model_Payment_Transaction setCreatedAt(string $value)
 *
 * @category    Mage
 * @package     Mage_Paypal
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Paypal_Model_Payment_Transaction extends Mage_Core_Model_Abstract
{
    /**
     * Whether to throw exceptions on different operations
     *
     * @var bool
     */
    protected $_isFailsafe = false;

    /**
     * Event object prefix
     *
     * @see Mage_Core_Model_Absctract::$_eventPrefix
     * @var string
     */
    protected $_eventPrefix = 'paypal_payment_transaction';

    /**
     * Event object prefix
     *
     * @see Mage_Core_Model_Absctract::$_eventObject
     * @var string
     */
    protected $_eventObject = 'paypal_payment_transaction';

    /**
     * Order website id
     *
     * @var int
     */
    protected $_orderWebsiteId = null;

    /**
     * Initialize resource model
     */
    protected function _construct()
    {
        $this->_init('Mage_Paypal_Model_Resource_Payment_Transaction');
        return parent::_construct();
    }

    /**
     * Transaction ID setter
     * @param string $txnId
     * @return Mage_Paypal_Model_Payment_Transaction
     */
    public function setTxnId($txnId)
    {
        $this->_verifyTxnId($txnId);
        return $this->setData('txn_id', $txnId);
    }

    /**
     * Check object before loading by by specified transaction ID
     * @param $txnId
     * @return Mage_Paypal_Model_Payment_Transaction
     */
    protected function _beforeLoadByTxnId($txnId)
    {
        Mage::dispatchEvent(
            $this->_eventPrefix . '_load_by_txn_id_before',
            $this->_getEventData() + array('txn_id' => $txnId)
        );
        return $this;
    }

    /**
     * Load self by specified transaction ID. Requires the valid payment object to be set
     * @param string $txnId
     * @return Mage_Paypal_Model_Payment_Transaction
     */
    public function loadByTxnId($txnId)
    {
        $this->_beforeLoadByTxnId($txnId);
        $this->getResource()->loadObjectByTxnId(
            $this, $txnId
        );
        $this->_afterLoadByTxnId();
        return $this;
    }

    /**
     * Check object after loading by by specified transaction ID
     * @param $txnId
     * @return Mage_Paypal_Model_Payment_Transaction
     */
    protected function _afterLoadByTxnId()
    {
        Mage::dispatchEvent($this->_eventPrefix . '_load_by_txn_id_after', $this->_getEventData());
        return $this;
    }


    /**
     * Additional information setter
     * Updates data inside the 'additional_information' array
     * Doesn't allow to set arrays
     *
     * @param string $key
     * @param mixed $value
     * @return Mage_Paypal_Model_Order_Payment_Transaction
     * @throws Mage_Core_Exception
     */
    public function setAdditionalInformation($key, $value)
    {
        if (is_object($value)) {
            Mage::throwException(Mage::helper('Mage_Paypal_Helper_Data')->__('Payment transactions disallow storing objects.'));
        }
        $info = $this->_getData('additional_information');
        if (!$info) {
            $info = array();
        }
        $info[$key] = $value;
        return $this->setData('additional_information', $info);
    }

    /**
     * Getter for entire additional_information value or one of its element by key
     * @param string $key
     * @return array|null|mixed
     */
    public function getAdditionalInformation($key = null)
    {
        $info = $this->_getData('additional_information');
        if (!$info) {
            $info = array();
        }
        if ($key) {
            return (isset($info[$key]) ? $info[$key] : null);
        }
        return $info;
    }

    /**
     * Unsetter for entire additional_information value or one of its element by key
     * @param string $key
     * @return Mage_Paypal_Model_Payment_Transaction
     */
    public function unsAdditionalInformation($key = null)
    {
        if ($key) {
            $info = $this->_getData('additional_information');
            if (is_array($info)) {
                unset($info[$key]);
            }
        } else {
            $info = array();
        }
        return $this->setData('additional_information', $info);
    }

    /**
     * Setter/Getter whether transaction is supposed to prevent exceptions on saving
     *
     * @param bool $failsafe
     */
    public function isFailsafe($setFailsafe = null)
    {
        if (null === $setFailsafe) {
            return $this->_isFailsafe;
        }
        $this->_isFailsafe = (bool)$setFailsafe;
        return $this;
    }

    /**
     * Verify data required for saving
     * @return Mage_Paypal_Model_Payment_Transaction
     * @throws Mage_Core_Exception
     */
    protected function _beforeSave()
    {
        if (!$this->getId()) {
            $this->setCreatedAt(Mage::getModel('Mage_Core_Model_Date')->gmtDate());
        }
        return parent::_beforeSave();
    }

    /**
     * Check whether specified transaction ID is valid
     * @param string $txnId
     * @throws Mage_Core_Exception
     */
    protected function _verifyTxnId($txnId)
    {
        if (null !== $txnId && 0 == strlen($txnId)) {
            Mage::throwException(Mage::helper('Mage_Paypal_Helper_Data')->__('You need to enter a transaction ID.'));
        }
    }

    /**
     * Make sure this object is a valid transaction
     * TODO for more restriction we can check for data consistency
     * @throws Mage_Core_Exception
     */
    protected function _verifyThisTransactionExists()
    {
        if (!$this->getId()) {
            Mage::throwException(Mage::helper('Mage_Paypal_Helper_Data')->__('This operation requires an existing transaction object.'));
        }
    }
}
