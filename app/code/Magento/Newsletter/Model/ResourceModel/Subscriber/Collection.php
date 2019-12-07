<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Newsletter\Model\ResourceModel\Subscriber;

use Magento\Newsletter\Model\Queue as ModelQueue;

/**
 * Newsletter subscribers collection
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 *
 * @api
 * @since 100.0.2
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Queue link table name
     *
     * @var string
     */
    protected $_queueLinkTable;

    /**
     * Store table name
     *
     * @var string
     */
    protected $_storeTable;

    /**
     * Queue joined flag
     *
     * @var boolean
     */
    protected $_queueJoinedFlag = false;

    /**
     * Flag that indicates apply of customers info on load
     *
     * @var boolean
     */
    protected $_showCustomersInfo = false;

    /**
     * Filter for count
     *
     * @var array
     */
    protected $_countFilterPart = [];

    /**
     * Customer Eav data
     *
     * @var   \Magento\Eav\Helper\Data
     */
    protected $_customerHelperData;

    /**
     * @param \Magento\Framework\Data\Collection\EntityFactory $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Eav\Helper\Data $customerHelperData
     * @param \Magento\Framework\DB\Adapter\AdapterInterface $connection
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactory $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Eav\Helper\Data $customerHelperData,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        $this->_customerHelperData = $customerHelperData;
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
    }

    /**
     * Constructor
     * Configures collection
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init(
            \Magento\Newsletter\Model\Subscriber::class,
            \Magento\Newsletter\Model\ResourceModel\Subscriber::class
        );
        $this->_queueLinkTable = $this->getTable('newsletter_queue_link');
        $this->_storeTable = $this->getTable('store');

        $this->_map['fields']['type'] = $this->getResource()->getConnection()->getCheckSql(
            'main_table.customer_id = 0',
            1,
            2
        );
        $this->_map['fields']['website_id'] = 'store.website_id';
        $this->_map['fields']['group_id'] = 'store.group_id';
        $this->_map['fields']['store_id'] = 'main_table.store_id';
    }

    /**
     * Set loading mode subscribers by queue
     *
     * @param ModelQueue $queue
     * @return $this
     */
    public function useQueue(ModelQueue $queue)
    {
        $this->getSelect()->join(
            ['link' => $this->_queueLinkTable],
            "link.subscriber_id = main_table.subscriber_id",
            []
        )->where(
            "link.queue_id = ? ",
            $queue->getId()
        );
        $this->_queueJoinedFlag = true;
        return $this;
    }

    /**
     * Set using of links to only unsendet letter subscribers.
     *
     * @return $this
     */
    public function useOnlyUnsent()
    {
        if ($this->_queueJoinedFlag) {
            $this->addFieldToFilter('link.letter_sent_at', ['null' => 1]);
        }

        return $this;
    }

    /**
     * Adds customer info to select
     *
     * @return $this
     */
    public function showCustomerInfo()
    {
        $this->getSelect()->joinLeft(
            [
                'customer' => $this->getTable('customer_entity')
            ],
            'main_table.customer_id = customer.entity_id',
            ['firstname', 'lastname']
        );
        return $this;
    }

    /**
     * Add type field expression to select
     *
     * @return $this
     */
    public function addSubscriberTypeField()
    {
        $this->getSelect()->columns(['type' => new \Zend_Db_Expr($this->_getMappedField('type'))]);
        return $this;
    }

    /**
     * Sets flag for customer info loading on load
     *
     * @return $this
     */
    public function showStoreInfo()
    {
        $this->getSelect()->join(
            ['store' => $this->_storeTable],
            'store.store_id = main_table.store_id',
            ['group_id', 'website_id']
        );

        return $this;
    }

    /**
     * Returns select count sql
     *
     * @return string
     */
    public function getSelectCountSql()
    {
        $select = parent::getSelectCountSql();
        $countSelect = clone $this->getSelect();

        $countSelect->reset(\Magento\Framework\DB\Select::HAVING);

        return $select;
    }

    /**
     * Load only subscribed customers
     *
     * @return $this
     */
    public function useOnlyCustomers()
    {
        $this->addFieldToFilter('main_table.customer_id', ['gt' => 0]);

        return $this;
    }

    /**
     * Show only with subscribed status
     *
     * @return $this
     */
    public function useOnlySubscribed()
    {
        $this->addFieldToFilter(
            'main_table.subscriber_status',
            \Magento\Newsletter\Model\Subscriber::STATUS_SUBSCRIBED
        );

        return $this;
    }

    /**
     * Filter collection by specified store ids
     *
     * @param int[]|int $storeIds
     * @return $this
     */
    public function addStoreFilter($storeIds)
    {
        $this->addFieldToFilter('main_table.store_id', ['in' => $storeIds]);
        return $this;
    }

    /**
     * Get queue joined flag
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getQueueJoinedFlag()
    {
        return $this->_queueJoinedFlag;
    }
}
