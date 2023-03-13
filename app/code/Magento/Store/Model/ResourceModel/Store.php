<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Model\ResourceModel;

use Magento\Framework\App\Cache\Type\Config as CacheTypeConfig;
use Magento\Framework\DB\Select;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context as DbContext;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store as ModelStore;

/**
 * Store Resource Model
 *
 * @api
 * @since 100.0.2
 */
class Store extends AbstractDb
{
    /**
     * @var CacheTypeConfig
     */
    protected $configCache;

    /**
     * @param DbContext $context
     * @param CacheTypeConfig $configCacheType
     */
    public function __construct(
        DbContext $context,
        CacheTypeConfig $configCacheType
    ) {
        $this->configCache = $configCacheType;
        parent::__construct($context);
    }

    /**
     * Define main table and primary key
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('store', 'store_id');
    }

    /**
     * Count number of all entities in the system
     *
     * By default won't count admin store
     *
     * @param bool $countAdmin
     * @return int
     */
    public function countAll($countAdmin = false)
    {
        $connection = $this->getConnection();
        $select = $connection->select()->from($this->getMainTable(), 'COUNT(*)');
        if (!$countAdmin) {
            $select->where(sprintf('%s <> %s', $connection->quoteIdentifier('code'), $connection->quote('admin')));
        }
        return (int)$connection->fetchOne($select);
    }

    /**
     * Initialize unique fields
     *
     * @return $this
     */
    protected function _initUniqueFields()
    {
        $this->_uniqueFields = [['field' => 'code', 'title' => __('Store with the same code')]];
        return $this;
    }

    /**
     * Update Store Group data after save store
     *
     * @param AbstractModel $object
     * @return $this
     */
    protected function _afterSave(AbstractModel $object)
    {
        parent::_afterSave($object);
        $this->_updateGroupDefaultStore($object->getGroupId(), $object->getId());
        $this->_changeGroup($object);

        return $this;
    }

    /**
     * Remove configuration data after delete store
     *
     * @param AbstractModel $model
     * @return $this
     */
    protected function _afterDelete(AbstractModel $model)
    {
        $where = [
            'scope = ?' => ScopeInterface::SCOPE_STORES,
            'scope_id = ?' => $model->getStoreId(),
        ];

        $this->getConnection()->delete($this->getTable('core_config_data'), $where);
        $this->configCache->clean();
        return $this;
    }

    /**
     * Update Default store for Store Group
     *
     * @param int $groupId
     * @param int $storeId
     * @return $this
     */
    protected function _updateGroupDefaultStore($groupId, $storeId)
    {
        $connection = $this->getConnection();

        $bindValues = ['group_id' => (int)$groupId];
        $select = $connection->select()->from(
            $this->getMainTable(),
            ['count' => 'COUNT(*)']
        )->where(
            'group_id = :group_id'
        );
        $count = $connection->fetchOne($select, $bindValues);

        if ($count == 1) {
            $bind = ['default_store_id' => (int)$storeId];
            $where = ['group_id = ?' => (int)$groupId];
            $connection->update($this->getTable('store_group'), $bind, $where);
        }

        return $this;
    }

    /**
     * Change store group for store
     *
     * @param AbstractModel $model
     * @return $this
     */
    protected function _changeGroup(AbstractModel $model)
    {
        if ($model->getOriginalGroupId() && $model->getGroupId() != $model->getOriginalGroupId()) {
            $connection = $this->getConnection();
            $select = $connection->select()->from(
                $this->getTable('store_group'),
                'default_store_id'
            )->where(
                $connection->quoteInto('group_id=?', $model->getOriginalGroupId())
            );
            $storeId = $connection->fetchOne($select);

            if ($storeId == $model->getId()) {
                $bind = ['default_store_id' => ModelStore::DEFAULT_STORE_ID];
                $where = ['group_id = ?' => $model->getOriginalGroupId()];
                $this->getConnection()->update($this->getTable('store_group'), $bind, $where);
            }
        }
        return $this;
    }

    /**
     * Read information about all stores
     *
     * @return array
     * @since 100.1.3
     */
    public function readAllStores()
    {
        $select = $this->getConnection()
            ->select()
            ->from($this->getTable($this->getMainTable()));
        return $this->getConnection()->fetchAll($select);
    }

    /**
     * Retrieve select object for load object data
     *
     * @param string $field
     * @param mixed $value
     * @param AbstractModel $object
     * @return Select
     */
    protected function _getLoadSelect($field, $value, $object)
    {
        $select = parent::_getLoadSelect($field, $value, $object);
        $select->order('sort_order');
        return $select;
    }
}
