<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Newsletter\Model\ResourceModel;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Math\Random;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Newsletter\Model\Subscriber as SubscriberModel;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Newsletter subscriber resource model
 *
 * @author Magento Core Team <core@magentocommerce.com>
 * @api
 * @since 100.0.2
 */
class Subscriber extends AbstractDb
{
    /**
     * DB connection
     *
     * @var AdapterInterface
     */
    protected $connection;

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
     * @var DateTime
     */
    protected $_date;

    /**
     * @var Random
     */
    protected $mathRandom;

    /**
     * Store manager
     *
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * Construct
     *
     * @param Context $context
     * @param DateTime $date
     * @param Random $mathRandom
     * @param string $connectionName
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Context $context,
        DateTime $date,
        Random $mathRandom,
        $connectionName = null,
        StoreManagerInterface $storeManager = null
    ) {
        $this->_date = $date;
        $this->mathRandom = $mathRandom;
        $this->storeManager = $storeManager ?: ObjectManager::getInstance()->get(StoreManagerInterface::class);
        parent::__construct($context, $connectionName);
    }

    /**
     * Initialize resource model. Get tablename from config
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('newsletter_subscriber', 'subscriber_id');
        $this->_subscriberLinkTable = $this->getTable('newsletter_queue_link');
        $this->connection = $this->getConnection();
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
     * Load by subscriber email
     *
     * @param string $email
     * @param int $websiteId
     * @return array
     * @since 100.4.0
     * @throws LocalizedException
     */
    public function loadBySubscriberEmail(string $email, int $websiteId): array
    {
        $storeIds = $this->storeManager->getWebsite($websiteId)->getStoreIds();
        $select = $this->connection->select()
            ->from($this->getMainTable())
            ->where('subscriber_email = ?', $email)
            ->where('store_id IN (?)', $storeIds)
            ->limit(1);

        $data = $this->connection->fetchRow($select);
        if (!$data) {
            return [];
        }

        return $data;
    }

    /**
     * Load by customer id
     *
     * @param int $customerId
     * @param int $websiteId
     * @return array
     * @since 100.4.0
     */
    public function loadByCustomerId(int $customerId, int $websiteId): array
    {
        $storeIds = $this->storeManager->getWebsite($websiteId)->getStoreIds();
        $select = $this->connection->select()
            ->from($this->getMainTable())
            ->where('customer_id = ?', $customerId)
            ->where('store_id IN (?)', $storeIds)
            ->limit(1);

        $data = $this->connection->fetchRow($select);
        if (!$data) {
            return [];
        }

        return $data;
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
     * @param SubscriberModel $subscriber
     * @param \Magento\Newsletter\Model\Queue $queue
     * @return $this
     * @throws LocalizedException
     */
    public function received(SubscriberModel $subscriber, \Magento\Newsletter\Model\Queue $queue)
    {
        $this->connection->beginTransaction();
        try {
            $data['letter_sent_at'] = $this->_date->gmtDate();
            $this->connection->update(
                $this->_subscriberLinkTable,
                $data,
                ['subscriber_id = ?' => $subscriber->getId(), 'queue_id = ?' => $queue->getId()]
            );
            $this->connection->commit();
        } catch (\Exception $e) {
            $this->connection->rollBack();
            throw new LocalizedException(__('We cannot mark as received subscriber.'));
        }
        return $this;
    }

    /**
     * Load subscriber from DB by email
     *
     * @param string $subscriberEmail
     * @return array
     * @deprecated 100.4.0 The subscription should be loaded by website id
     * @see loadBySubscriberEmail
     */
    public function loadByEmail($subscriberEmail)
    {
        $websiteId = (int)$this->storeManager->getWebsite()->getId();
        return $this->loadBySubscriberEmail((string)$subscriberEmail, $websiteId);
    }

    /**
     * Load subscriber by customer
     *
     * @param CustomerInterface $customer
     * @return array
     * @deprecated 100.4.0 The subscription should be loaded by website id
     * @see loadByCustomerId
     */
    public function loadByCustomerData(CustomerInterface $customer)
    {
        $websiteId = (int)$this->storeManager->getWebsite()->getId();
        $data = $this->loadByCustomerId((int)$customer->getId(), $websiteId);
        if (empty($data)) {
            $data = $this->loadBySubscriberEmail((string)$customer->getEmail(), $websiteId);
        }

        return $data;
    }
}
