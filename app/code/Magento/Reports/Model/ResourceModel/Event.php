<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Reports\Model\ResourceModel;

/**
 * Report events resource model
 * @api
 * @since 100.0.2
 */
class Event extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Core store config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param string $connectionName
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
        $this->_scopeConfig = $scopeConfig;
        $this->_storeManager = $storeManager;
    }

    /**
     * Initialize main table and identifier field. Set main entity table name and primary key field name.
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('report_event', 'event_id');
    }

    /**
     * Update customer type after customer login
     *
     * @param \Magento\Reports\Model\Event $model
     * @param int $visitorId
     * @param int $customerId
     * @param array $types
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function updateCustomerType(\Magento\Reports\Model\Event $model, $visitorId, $customerId, $types = [])
    {
        if ($types) {
            $this->getConnection()->update(
                $this->getMainTable(),
                ['subject_id' => (int) $customerId, 'subtype' => 0],
                ['subject_id = ?' => (int) $visitorId, 'subtype = ?' => 1, 'event_type_id IN(?)' => $types]
            );
        }
        return $this;
    }

    /**
     * Add events log to a collection
     * The collection id field is used without corellation, so it must be unique.
     * DESC ordering by event will be added to the collection
     *
     * @param \Magento\Framework\Data\Collection\AbstractDb $collection
     * @param int $eventTypeId
     * @param int $eventSubjectId
     * @param int $subtype
     * @param array $skipIds
     * @return $this
     */
    public function applyLogToCollection(
        \Magento\Framework\Data\Collection\AbstractDb $collection,
        $eventTypeId,
        $eventSubjectId,
        $subtype,
        $skipIds = []
    ) {
        $idFieldName = $collection->getResource()->getIdFieldName();
        $predefinedStoreIds = ($collection->getStoreId() === null)
            ? null
            : [$collection->getStoreId()];

        $derivedSelect = $this->getConnection()
            ->select()
            ->from(
                $this->getTable('report_event'),
                ['event_id' => new \Zend_Db_Expr('MAX(event_id)'), 'object_id']
            )
            ->where('event_type_id = ?', (int) $eventTypeId)
            ->where('subject_id = ?', (int) $eventSubjectId)
            ->where('subtype = ?', (int) $subtype)
            ->where('store_id IN(?)', $this->getCurrentStoreIds($predefinedStoreIds))
            ->group('object_id');

        if ($skipIds) {
            if (!is_array($skipIds)) {
                $skipIds = [(int) $skipIds];
            }
            $derivedSelect->where('object_id NOT IN(?)', $skipIds);
        }

        $collection->getSelect()->joinInner(
            ['evt' => new \Zend_Db_Expr("({$derivedSelect})")],
            "{$idFieldName} = evt.object_id",
            []
        )->order('evt.event_id ' . \Magento\Framework\DB\Select::SQL_DESC);

        return $this;
    }

    /**
     * Obtain all current store ids, depending on configuration
     *
     * @param null|array $predefinedStoreIds
     * @return array
     */
    public function getCurrentStoreIds(array $predefinedStoreIds = null)
    {
        $stores = [];
        // get all or specified stores
        if ($predefinedStoreIds !== null) {
            $stores = $predefinedStoreIds;
        } elseif ($this->_storeManager->getStore()->getId() == 0) {
            foreach ($this->_storeManager->getStores() as $store) {
                $stores[] = $store->getId();
            }
        } else {
            // get all stores, required by configuration in current store scope
            $productsScope = $this->_scopeConfig->getValue(
                'catalog/recently_products/scope',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
            switch ($productsScope) {
                case 'website':
                    $resourceStore = $this->_storeManager->getStore()->getWebsite()->getStores();
                    break;
                case 'group':
                    $resourceStore = $this->_storeManager->getStore()->getGroup()->getStores();
                    break;
                default:
                    $resourceStore = [$this->_storeManager->getStore()];
                    break;
            }

            foreach ($resourceStore as $store) {
                $stores[] = $store->getId();
            }
        }
        foreach ($stores as $key => $store) {
            $stores[$key] = (int) $store;
        }

        return $stores;
    }

    /**
     * Clean report event table
     *
     * @param \Magento\Reports\Model\Event $object
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function clean(\Magento\Reports\Model\Event $object)
    {
        while (true) {
            $select = $this->getConnection()->select()->from(
                ['event_table' => $this->getMainTable()],
                ['event_id']
            )->joinLeft(
                ['visitor_table' => $this->getTable('customer_visitor')],
                'event_table.subject_id = visitor_table.visitor_id',
                []
            )->where('visitor_table.visitor_id IS NULL')
                ->where('event_table.subtype = ?', 1)
                ->limit(1000);
            $eventIds = $this->getConnection()->fetchCol($select);

            if (!$eventIds) {
                break;
            }

            $this->getConnection()->delete($this->getMainTable(), ['event_id IN(?)' => $eventIds]);
        }
        return $this;
    }
}
