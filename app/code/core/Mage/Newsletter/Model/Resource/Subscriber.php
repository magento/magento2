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
 * @package     Mage_Newsletter
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Newsletter subscriber resource model
 *
 * @category    Mage
 * @package     Mage_Newsletter
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Newsletter_Model_Resource_Subscriber extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * DB read connection
     *
     * @var Zend_Db_Adapter_Abstract
     */
    protected $_read;

    /**
     * DB write connection
     *
     * @var Zend_Db_Adapter_Abstract
     */
    protected $_write;

    /**
     * Name of subscriber link DB table
     *
     * @var string
     */
    protected $_subscriberLinkTable;

    /**
     * Name of scope for error messages
     *
     * @var string
     */
    protected $_messagesScope          = 'newsletter/session';

    /**
     * Initialize resource model
     * Get tablename from config
     *
     */
    protected function _construct()
    {
        $this->_init('newsletter_subscriber', 'subscriber_id');
        $this->_subscriberLinkTable = $this->getTable('newsletter_queue_link');
        $this->_read = $this->_getReadAdapter();
        $this->_write = $this->_getWriteAdapter();
    }

    /**
     * Set error messages scope
     *
     * @param string $scope
     */
    public function setMessagesScope($scope)
    {
        $this->_messagesScope = $scope;
    }

    /**
     * Load subscriber from DB by email
     *
     * @param string $subscriberEmail
     * @return array
     */
    public function loadByEmail($subscriberEmail)
    {
        $select = $this->_read->select()
            ->from($this->getMainTable())
            ->where('subscriber_email=:subscriber_email');

        $result = $this->_read->fetchRow($select, array('subscriber_email'=>$subscriberEmail));

        if (!$result) {
            return array();
        }

        return $result;
    }

    /**
     * Load subscriber by customer
     *
     * @param Mage_Customer_Model_Customer $customer
     * @return array
     */
    public function loadByCustomer(Mage_Customer_Model_Customer $customer)
    {
        $select = $this->_read->select()
            ->from($this->getMainTable())
            ->where('customer_id=:customer_id');

        $result = $this->_read->fetchRow($select, array('customer_id'=>$customer->getId()));

        if ($result) {
            return $result;
        }

        $select = $this->_read->select()
            ->from($this->getMainTable())
            ->where('subscriber_email=:subscriber_email');

        $result = $this->_read->fetchRow($select, array('subscriber_email'=>$customer->getEmail()));

        if ($result) {
            return $result;
        }

        return array();
    }

    /**
     * Generates random code for subscription confirmation
     *
     * @return string
     */
    protected function _generateRandomCode()
    {
        return Mage::helper('Mage_Core_Helper_Data')->uniqHash();
    }

    /**
     * Updates data when subscriber received
     *
     * @param Mage_Newsletter_Model_Subscriber $subscriber
     * @param Mage_Newsletter_Model_Queue $queue
     * @return Mage_Newsletter_Model_Resource_Subscriber
     */
    public function received(Mage_Newsletter_Model_Subscriber $subscriber, Mage_Newsletter_Model_Queue $queue)
    {
        $this->_write->beginTransaction();
        try {
            $data['letter_sent_at'] = Mage::getSingleton('Mage_Core_Model_Date')->gmtDate();
            $this->_write->update($this->_subscriberLinkTable, $data, array(
                'subscriber_id = ?' => $subscriber->getId(),
                'queue_id = ?' => $queue->getId()
            ));
            $this->_write->commit();
        }
        catch (Exception $e) {
            $this->_write->rollBack();
            Mage::throwException(Mage::helper('Mage_Newsletter_Helper_Data')->__('Cannot mark as received subscriber.'));
        }
        return $this;
    }
}
