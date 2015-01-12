<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Newsletter\Model\Resource;

/**
 * Newsletter subscriber resource model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Subscriber extends \Magento\Framework\Model\Resource\Db\AbstractDb
{
    /**
     * DB read connection
     *
     * @var \Zend_Db_Adapter_Abstract
     */
    protected $_read;

    /**
     * DB write connection
     *
     * @var \Zend_Db_Adapter_Abstract
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
    protected $_messagesScope = 'newsletter/session';

    /**
     * Date
     *
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_date;

    /**
     * @var \Magento\Framework\Math\Random
     */
    protected $mathRandom;

    /**
     * Construct
     *
     * @param \Magento\Framework\App\Resource $resource
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Magento\Framework\Math\Random $mathRandom
     */
    public function __construct(
        \Magento\Framework\App\Resource $resource,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\Math\Random $mathRandom
    ) {
        $this->_date = $date;
        $this->mathRandom = $mathRandom;
        parent::__construct($resource);
    }

    /**
     * Initialize resource model
     * Get tablename from config
     *
     * @return void
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
     * @return void
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
        $select = $this->_read->select()->from($this->getMainTable())->where('subscriber_email=:subscriber_email');

        $result = $this->_read->fetchRow($select, ['subscriber_email' => $subscriberEmail]);

        if (!$result) {
            return [];
        }

        return $result;
    }

    /**
     * Load subscriber by customer
     *
     * @param \Magento\Customer\Api\Data\CustomerInterface $customer
     * @return array
     */
    public function loadByCustomerData(\Magento\Customer\Api\Data\CustomerInterface $customer)
    {
        $select = $this->_read->select()->from($this->getMainTable())->where('customer_id=:customer_id');

        $result = $this->_read->fetchRow($select, ['customer_id' => $customer->getId()]);

        if ($result) {
            return $result;
        }

        $select = $this->_read->select()->from($this->getMainTable())->where('subscriber_email=:subscriber_email');

        $result = $this->_read->fetchRow($select, ['subscriber_email' => $customer->getEmail()]);

        if ($result) {
            return $result;
        }

        return [];
    }

    /**
     * Generates random code for subscription confirmation
     *
     * @return string
     */
    protected function _generateRandomCode()
    {
        return $this->mathRandom->getUniqueHash();
    }

    /**
     * Updates data when subscriber received
     *
     * @param \Magento\Newsletter\Model\Subscriber $subscriber
     * @param \Magento\Newsletter\Model\Queue $queue
     * @return $this
     * @throws \Magento\Framework\Model\Exception
     */
    public function received(\Magento\Newsletter\Model\Subscriber $subscriber, \Magento\Newsletter\Model\Queue $queue)
    {
        $this->_write->beginTransaction();
        try {
            $data['letter_sent_at'] = $this->_date->gmtDate();
            $this->_write->update(
                $this->_subscriberLinkTable,
                $data,
                ['subscriber_id = ?' => $subscriber->getId(), 'queue_id = ?' => $queue->getId()]
            );
            $this->_write->commit();
        } catch (\Exception $e) {
            $this->_write->rollBack();
            throw new \Magento\Framework\Model\Exception(__('We cannot mark as received subscriber.'));
        }
        return $this;
    }
}
