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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Reports\Model\Resource;

/**
 * Report events resource model
 */
class Event extends \Magento\Framework\Model\Resource\Db\AbstractDb
{
    /**
     * Core store config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Magento\Framework\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @param \Magento\Framework\App\Resource $resource
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Framework\App\Resource $resource,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\StoreManagerInterface $storeManager
    ) {
        parent::__construct($resource);
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
     */
    public function updateCustomerType(\Magento\Reports\Model\Event $model, $visitorId, $customerId, $types = array())
    {
        if ($types) {
            $this->_getWriteAdapter()->update(
                $this->getMainTable(),
                array('subject_id' => (int) $customerId, 'subtype' => 0),
                array('subject_id = ?' => (int) $visitorId, 'subtype = ?' => 1, 'event_type_id IN(?)' => $types)
            );
        }
        return $this;
    }

    /**
     * Add events log to a collection
     * The collection id field is used without corellation, so it must be unique.
     * DESC ordering by event will be added to the collection
     *
     * @param \Magento\Framework\Data\Collection\Db $collection
     * @param int $eventTypeId
     * @param int $eventSubjectId
     * @param int $subtype
     * @param array $skipIds
     * @return $this
     */
    public function applyLogToCollection(
        \Magento\Framework\Data\Collection\Db $collection,
        $eventTypeId,
        $eventSubjectId,
        $subtype,
        $skipIds = array()
    ) {
        $idFieldName = $collection->getResource()->getIdFieldName();

        $derivedSelect = $this->getReadConnection()
            ->select()
            ->from(
                $this->getTable('report_event'),
                array('event_id' => new \Zend_Db_Expr('MAX(event_id)'), 'object_id')
            )
            ->where('event_type_id = ?', (int) $eventTypeId)
            ->where('subject_id = ?', (int) $eventSubjectId)
            ->where('subtype = ?', (int) $subtype)
            ->where('store_id IN(?)', $this->getCurrentStoreIds())
            ->group('object_id');

        if ($skipIds) {
            if (!is_array($skipIds)) {
                $skipIds = array((int) $skipIds);
            }
            $derivedSelect->where('object_id NOT IN(?)', $skipIds);
        }

        $collection->getSelect()->joinInner(
            array('evt' => new \Zend_Db_Expr("({$derivedSelect})")),
            "{$idFieldName} = evt.object_id",
            array()
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
        $stores = array();
        // get all or specified stores
        if ($this->_storeManager->getStore()->getId() == 0) {
            if (null !== $predefinedStoreIds) {
                $stores = $predefinedStoreIds;
            } else {
                foreach ($this->_storeManager->getStores() as $store) {
                    $stores[] = $store->getId();
                }
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
                    $resourceStore = array($this->_storeManager->getStore());
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
     */
    public function clean(\Magento\Reports\Model\Event $object)
    {
        while (true) {
            $select = $this->_getReadAdapter()->select()->from(
                array('event_table' => $this->getMainTable()),
                array('event_id')
            )->joinLeft(
                array('visitor_table' => $this->getTable('log_visitor')),
                'event_table.subject_id = visitor_table.visitor_id',
                array()
            )->where('visitor_table.visitor_id IS NULL')
                ->where('event_table.subtype = ?', 1)
                ->limit(1000);
            $eventIds = $this->_getReadAdapter()->fetchCol($select);

            if (!$eventIds) {
                break;
            }

            $this->_getWriteAdapter()->delete($this->getMainTable(), array('event_id IN(?)' => $eventIds));
        }
        return $this;
    }
}
