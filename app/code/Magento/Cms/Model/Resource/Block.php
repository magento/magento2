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
 * CMS block model
 */
class Block extends \Magento\Model\Resource\Db\AbstractDb
{
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
     * Construct
     *
     * @param \Magento\App\Resource $resource
     * @param \Magento\Stdlib\DateTime\DateTime $date
     * @param \Magento\Core\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\App\Resource $resource,
        \Magento\Stdlib\DateTime\DateTime $date,
        \Magento\Core\Model\StoreManagerInterface $storeManager
    ) {
        parent::__construct($resource);
        $this->_storeManager = $storeManager;
        $this->_date = $date;
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('cms_block', 'block_id');
    }

    /**
     * Process block data before deleting
     *
     * @param \Magento\Model\AbstractModel $object
     * @return \Magento\Cms\Model\Resource\Page
     */
    protected function _beforeDelete(\Magento\Model\AbstractModel $object)
    {
        $condition = array('block_id = ?' => (int)$object->getId());

        $this->_getWriteAdapter()->delete($this->getTable('cms_block_store'), $condition);

        return parent::_beforeDelete($object);
    }

    /**
     * Perform operations before object save
     *
     * @param \Magento\Model\AbstractModel $object
     * @return $this
     * @throws \Magento\Model\Exception
     */
    protected function _beforeSave(\Magento\Model\AbstractModel $object)
    {
        if (!$this->getIsUniqueBlockToStores($object)) {
            throw new \Magento\Model\Exception(
                __('A block identifier with the same properties already exists in the selected store.')
            );
        }

        if (!$object->getId()) {
            $object->setCreationTime($this->_date->gmtDate());
        }
        $object->setUpdateTime($this->_date->gmtDate());
        return $this;
    }

    /**
     * Perform operations after object save
     *
     * @param \Magento\Model\AbstractModel $object
     * @return $this
     */
    protected function _afterSave(\Magento\Model\AbstractModel $object)
    {
        $oldStores = $this->lookupStoreIds($object->getId());
        $newStores = (array)$object->getStores();

        $table = $this->getTable('cms_block_store');
        $insert = array_diff($newStores, $oldStores);
        $delete = array_diff($oldStores, $newStores);

        if ($delete) {
            $where = array('block_id = ?' => (int)$object->getId(), 'store_id IN (?)' => $delete);

            $this->_getWriteAdapter()->delete($table, $where);
        }

        if ($insert) {
            $data = array();

            foreach ($insert as $storeId) {
                $data[] = array('block_id' => (int)$object->getId(), 'store_id' => (int)$storeId);
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
            $object->setData('stores', $stores);
        }

        return parent::_afterLoad($object);
    }

    /**
     * Retrieve select object for load object data
     *
     * @param string $field
     * @param mixed $value
     * @param \Magento\Cms\Model\Block $object
     * @return \Zend_Db_Select
     */
    protected function _getLoadSelect($field, $value, $object)
    {
        $select = parent::_getLoadSelect($field, $value, $object);

        if ($object->getStoreId()) {
            $stores = array((int)$object->getStoreId(), \Magento\Core\Model\Store::DEFAULT_STORE_ID);

            $select->join(
                array('cbs' => $this->getTable('cms_block_store')),
                $this->getMainTable() . '.block_id = cbs.block_id',
                array('store_id')
            )->where(
                'is_active = ?',
                1
            )->where(
                'cbs.store_id in (?)',
                $stores
            )->order(
                'store_id DESC'
            )->limit(
                1
            );
        }

        return $select;
    }

    /**
     * Check for unique of identifier of block to selected store(s).
     *
     * @param \Magento\Model\AbstractModel $object
     * @return bool
     */
    public function getIsUniqueBlockToStores(\Magento\Model\AbstractModel $object)
    {
        if ($this->_storeManager->hasSingleStore()) {
            $stores = array(\Magento\Core\Model\Store::DEFAULT_STORE_ID);
        } else {
            $stores = (array)$object->getData('stores');
        }

        $select = $this->_getReadAdapter()->select()->from(
            array('cb' => $this->getMainTable())
        )->join(
            array('cbs' => $this->getTable('cms_block_store')),
            'cb.block_id = cbs.block_id',
            array()
        )->where(
            'cb.identifier = ?',
            $object->getData('identifier')
        )->where(
            'cbs.store_id IN (?)',
            $stores
        );

        if ($object->getId()) {
            $select->where('cb.block_id <> ?', $object->getId());
        }

        if ($this->_getReadAdapter()->fetchRow($select)) {
            return false;
        }

        return true;
    }

    /**
     * Get store ids to which specified item is assigned
     *
     * @param int $id
     * @return array
     */
    public function lookupStoreIds($id)
    {
        $adapter = $this->_getReadAdapter();

        $select = $adapter->select()->from(
            $this->getTable('cms_block_store'),
            'store_id'
        )->where(
            'block_id = :block_id'
        );

        $binds = array(':block_id' => (int)$id);

        return $adapter->fetchCol($select, $binds);
    }
}
