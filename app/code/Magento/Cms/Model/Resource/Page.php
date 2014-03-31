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
 * @category    Magento
 * @package     Magento_Cms
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Cms\Model\Resource;

/**
 * Cms page mysql resource
 */
class Page extends \Magento\Model\Resource\Db\AbstractDb
{
    /**
     * Store model
     *
     * @var null|\Magento\Core\Model\Store
     */
    protected $_store = null;

    /**
     * @var \Magento\Stdlib\DateTime\DateTime
     */
    protected $_date;

    /**
     * Store manager
     *
     * @var \Magento\Core\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Stdlib\DateTime
     */
    protected $dateTime;

    /**
     * @var \Magento\Filter\FilterManager
     */
    protected $filter;

    /**
     * Construct
     *
     * @param \Magento\App\Resource $resource
     * @param \Magento\Stdlib\DateTime\DateTime $date
     * @param \Magento\Core\Model\StoreManagerInterface $storeManager
     * @param \Magento\Stdlib\DateTime $dateTime
     * @param \Magento\Filter\FilterManager $filter
     */
    public function __construct(
        \Magento\App\Resource $resource,
        \Magento\Stdlib\DateTime\DateTime $date,
        \Magento\Core\Model\StoreManagerInterface $storeManager,
        \Magento\Stdlib\DateTime $dateTime,
        \Magento\Filter\FilterManager $filter
    ) {
        parent::__construct($resource);
        $this->_date = $date;
        $this->_storeManager = $storeManager;
        $this->dateTime = $dateTime;
        $this->filter = $filter;
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('cms_page', 'page_id');
    }

    /**
     * Process page data before deleting
     *
     * @param \Magento\Model\AbstractModel $object
     * @return $this
     */
    protected function _beforeDelete(\Magento\Model\AbstractModel $object)
    {
        $condition = array('page_id = ?' => (int)$object->getId());

        $this->_getWriteAdapter()->delete($this->getTable('cms_page_store'), $condition);

        return parent::_beforeDelete($object);
    }

    /**
     * Process page data before saving
     *
     * @param \Magento\Model\AbstractModel $object
     * @return $this
     * @throws \Magento\Model\Exception
     */
    protected function _beforeSave(\Magento\Model\AbstractModel $object)
    {
        /*
         * For two attributes which represent timestamp data in DB
         * we should make converting such as:
         * If they are empty we need to convert them into DB
         * type NULL so in DB they will be empty and not some default value
         */
        foreach (array('custom_theme_from', 'custom_theme_to') as $field) {
            $value = !$object->getData($field) ? null : $object->getData($field);
            $object->setData($field, $this->dateTime->formatDate($value));
        }

        if (!$object->getData('identifier')) {
            $object->setData('identifier', $this->filter->translitUrl($object->getData('title')));
        }

        if (!$this->getIsUniquePageToStores($object)) {
            throw new \Magento\Model\Exception(__('A page URL key for specified store already exists.'));
        }

        if (!$this->isValidPageIdentifier($object)) {
            throw new \Magento\Model\Exception(__('The page URL key contains capital letters or disallowed symbols.'));
        }

        if ($this->isNumericPageIdentifier($object)) {
            throw new \Magento\Model\Exception(__('The page URL key cannot be made of only numbers.'));
        }

        // modify create / update dates
        if ($object->isObjectNew() && !$object->hasCreationTime()) {
            $object->setCreationTime($this->_date->gmtDate());
        }

        $object->setUpdateTime($this->_date->gmtDate());

        return parent::_beforeSave($object);
    }

    /**
     * Assign page to store views
     *
     * @param \Magento\Model\AbstractModel $object
     * @return $this
     */
    protected function _afterSave(\Magento\Model\AbstractModel $object)
    {
        $oldStores = $this->lookupStoreIds($object->getId());
        $newStores = (array)$object->getStores();
        if (empty($newStores)) {
            $newStores = (array)$object->getStoreId();
        }
        $table = $this->getTable('cms_page_store');
        $insert = array_diff($newStores, $oldStores);
        $delete = array_diff($oldStores, $newStores);

        if ($delete) {
            $where = array('page_id = ?' => (int)$object->getId(), 'store_id IN (?)' => $delete);

            $this->_getWriteAdapter()->delete($table, $where);
        }

        if ($insert) {
            $data = array();

            foreach ($insert as $storeId) {
                $data[] = array('page_id' => (int)$object->getId(), 'store_id' => (int)$storeId);
            }

            $this->_getWriteAdapter()->insertMultiple($table, $data);
        }

        return parent::_afterSave($object);
    }

    /**
     * Load an object using 'identifier' field if there's no field specified and value is not numeric
     *
     * @param \Magento\Model\AbstractModel $object
     * @param mixed $value
     * @param string $field
     * @return $this
     */
    public function load(\Magento\Model\AbstractModel $object, $value, $field = null)
    {
        if (!is_numeric($value) && is_null($field)) {
            $field = 'identifier';
        }

        return parent::load($object, $value, $field);
    }

    /**
     * Perform operations after object load
     *
     * @param \Magento\Model\AbstractModel $object
     * @return $this
     */
    protected function _afterLoad(\Magento\Model\AbstractModel $object)
    {
        if ($object->getId()) {
            $stores = $this->lookupStoreIds($object->getId());

            $object->setData('store_id', $stores);
        }

        return parent::_afterLoad($object);
    }

    /**
     * Retrieve select object for load object data
     *
     * @param string $field
     * @param mixed $value
     * @param \Magento\Cms\Model\Page $object
     * @return \Zend_Db_Select
     */
    protected function _getLoadSelect($field, $value, $object)
    {
        $select = parent::_getLoadSelect($field, $value, $object);

        if ($object->getStoreId()) {
            $storeIds = array(\Magento\Core\Model\Store::DEFAULT_STORE_ID, (int)$object->getStoreId());
            $select->join(
                array('cms_page_store' => $this->getTable('cms_page_store')),
                $this->getMainTable() . '.page_id = cms_page_store.page_id',
                array()
            )->where(
                'is_active = ?',
                1
            )->where(
                'cms_page_store.store_id IN (?)',
                $storeIds
            )->order(
                'cms_page_store.store_id DESC'
            )->limit(
                1
            );
        }

        return $select;
    }

    /**
     * Retrieve load select with filter by identifier, store and activity
     *
     * @param string $identifier
     * @param int|array $store
     * @param int $isActive
     * @return \Magento\DB\Select
     */
    protected function _getLoadByIdentifierSelect($identifier, $store, $isActive = null)
    {
        $select = $this->_getReadAdapter()->select()->from(
            array('cp' => $this->getMainTable())
        )->join(
            array('cps' => $this->getTable('cms_page_store')),
            'cp.page_id = cps.page_id',
            array()
        )->where(
            'cp.identifier = ?',
            $identifier
        )->where(
            'cps.store_id IN (?)',
            $store
        );

        if (!is_null($isActive)) {
            $select->where('cp.is_active = ?', $isActive);
        }

        return $select;
    }

    /**
     * Check for unique of identifier of page to selected store(s).
     *
     * @param \Magento\Model\AbstractModel $object
     * @return bool
     */
    public function getIsUniquePageToStores(\Magento\Model\AbstractModel $object)
    {
        if ($this->_storeManager->hasSingleStore() || !$object->hasStores()) {
            $stores = array(\Magento\Core\Model\Store::DEFAULT_STORE_ID);
        } else {
            $stores = (array)$object->getData('stores');
        }

        $select = $this->_getLoadByIdentifierSelect($object->getData('identifier'), $stores);

        if ($object->getId()) {
            $select->where('cps.page_id <> ?', $object->getId());
        }

        if ($this->_getWriteAdapter()->fetchRow($select)) {
            return false;
        }

        return true;
    }

    /**
     *  Check whether page identifier is numeric
     *
     * @param \Magento\Model\AbstractModel $object
     * @return bool
     */
    protected function isNumericPageIdentifier(\Magento\Model\AbstractModel $object)
    {
        return preg_match('/^[0-9]+$/', $object->getData('identifier'));
    }

    /**
     *  Check whether page identifier is valid
     *
     * @param \Magento\Model\AbstractModel $object
     * @return bool
     */
    protected function isValidPageIdentifier(\Magento\Model\AbstractModel $object)
    {
        return preg_match('/^[a-z0-9][a-z0-9_\/-]+(\.[a-z0-9_-]+)?$/', $object->getData('identifier'));
    }

    /**
     * Check if page identifier exist for specific store
     * return page id if page exists
     *
     * @param string $identifier
     * @param int $storeId
     * @return int
     */
    public function checkIdentifier($identifier, $storeId)
    {
        $stores = array(\Magento\Core\Model\Store::DEFAULT_STORE_ID, $storeId);
        $select = $this->_getLoadByIdentifierSelect($identifier, $stores, 1);
        $select->reset(\Zend_Db_Select::COLUMNS)->columns('cp.page_id')->order('cps.store_id DESC')->limit(1);

        return $this->_getReadAdapter()->fetchOne($select);
    }

    /**
     * Retrieves cms page title from DB by passed identifier.
     *
     * @param string $identifier
     * @return string|false
     */
    public function getCmsPageTitleByIdentifier($identifier)
    {
        $stores = array(\Magento\Core\Model\Store::DEFAULT_STORE_ID);
        if ($this->_store) {
            $stores[] = (int)$this->getStore()->getId();
        }

        $select = $this->_getLoadByIdentifierSelect($identifier, $stores);
        $select->reset(\Zend_Db_Select::COLUMNS)->columns('cp.title')->order('cps.store_id DESC')->limit(1);

        return $this->_getReadAdapter()->fetchOne($select);
    }

    /**
     * Retrieves cms page title from DB by passed id.
     *
     * @param string $id
     * @return string|false
     */
    public function getCmsPageTitleById($id)
    {
        $adapter = $this->_getReadAdapter();

        $select = $adapter->select()->from($this->getMainTable(), 'title')->where('page_id = :page_id');

        $binds = array('page_id' => (int)$id);

        return $adapter->fetchOne($select, $binds);
    }

    /**
     * Retrieves cms page identifier from DB by passed id.
     *
     * @param string $id
     * @return string|false
     */
    public function getCmsPageIdentifierById($id)
    {
        $adapter = $this->_getReadAdapter();

        $select = $adapter->select()->from($this->getMainTable(), 'identifier')->where('page_id = :page_id');

        $binds = array('page_id' => (int)$id);

        return $adapter->fetchOne($select, $binds);
    }

    /**
     * Get store ids to which specified item is assigned
     *
     * @param int $pageId
     * @return array
     */
    public function lookupStoreIds($pageId)
    {
        $adapter = $this->_getReadAdapter();

        $select = $adapter->select()->from(
            $this->getTable('cms_page_store'),
            'store_id'
        )->where(
            'page_id = ?',
            (int)$pageId
        );

        return $adapter->fetchCol($select);
    }

    /**
     * Set store model
     *
     * @param \Magento\Core\Model\Store $store
     * @return $this
     */
    public function setStore($store)
    {
        $this->_store = $store;
        return $this;
    }

    /**
     * Retrieve store model
     *
     * @return \Magento\Core\Model\Store
     */
    public function getStore()
    {
        return $this->_storeManager->getStore($this->_store);
    }
}
