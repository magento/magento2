<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Model\ResourceModel;

/**
 * Store group resource model
 *
 * @api
 * @since 100.0.2
 */
class Group extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Define main table
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('store_group', 'group_id');
    }

    /**
     * Update default store group for website
     *
     * @param \Magento\Framework\Model\AbstractModel $model
     * @return $this
     */
    protected function _afterSave(\Magento\Framework\Model\AbstractModel $model)
    {
        $this->_updateStoreWebsite($model->getId(), $model->getWebsiteId());
        $this->_updateWebsiteDefaultGroup($model->getWebsiteId(), $model->getId());
        $this->_changeWebsite($model);

        return $this;
    }

    /**
     * Initialize unique fields
     *
     * @return $this
     * @since 100.2.0
     */
    protected function _initUniqueFields()
    {
        $this->_uniqueFields = [['field' => 'code', 'title' => __('Group with the same code')]];
        return $this;
    }

    /**
     * Update default store group for website
     *
     * @param int $websiteId
     * @param int $groupId
     * @return $this
     */
    protected function _updateWebsiteDefaultGroup($websiteId, $groupId)
    {
        $select = $this->getConnection()->select()->from(
            $this->getMainTable(),
            'COUNT(*)'
        )->where(
            'website_id = :website'
        );
        $count = $this->getConnection()->fetchOne($select, ['website' => $websiteId]);

        if ($count == 1) {
            $bind = ['default_group_id' => $groupId];
            $where = ['website_id = ?' => $websiteId];
            $this->getConnection()->update($this->getTable('store_website'), $bind, $where);
        }
        return $this;
    }

    /**
     * Change store group website
     *
     * @param \Magento\Framework\Model\AbstractModel $model
     * @return $this
     */
    protected function _changeWebsite(\Magento\Framework\Model\AbstractModel $model)
    {
        if ($model->getOriginalWebsiteId() && $model->getWebsiteId() != $model->getOriginalWebsiteId()) {
            $select = $this->getConnection()->select()->from(
                $this->getTable('store_website'),
                'default_group_id'
            )->where(
                'website_id = :website_id'
            );
            $groupId = $this->getConnection()->fetchOne(
                $select,
                ['website_id' => $model->getOriginalWebsiteId()]
            );

            if ($groupId == $model->getId()) {
                $bind = ['default_group_id' => 0];
                $where = ['website_id = ?' => $model->getOriginalWebsiteId()];
                $this->getConnection()->update($this->getTable('store_website'), $bind, $where);
            }
        }
        return $this;
    }

    /**
     * Update website for stores that assigned to store group
     *
     * @param int $groupId
     * @param int $websiteId
     * @return $this
     */
    protected function _updateStoreWebsite($groupId, $websiteId)
    {
        $bind = ['website_id' => $websiteId];
        $where = ['group_id = ?' => $groupId];
        $this->getConnection()->update($this->getTable('store'), $bind, $where);
        return $this;
    }

    /**
     * Save default store for store group
     *
     * @param int $groupId
     * @param int $storeId
     * @return $this
     */
    protected function _saveDefaultStore($groupId, $storeId)
    {
        $bind = ['default_store_id' => $storeId];
        $where = ['group_id = ?' => $groupId];
        $this->getConnection()->update($this->getMainTable(), $bind, $where);

        return $this;
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
        $select = $connection->select()->from(['main' => $this->getMainTable()], 'COUNT(*)');
        if (!$countAdmin) {
            $select->joinLeft(
                ['store_website' => $this->getTable('store_website')],
                'store_website.website_id = main.website_id',
                null
            )->where(
                sprintf('%s <> %s', $connection->quoteIdentifier('code'), $connection->quote('admin'))
            );
        }
        return (int)$connection->fetchOne($select);
    }
}
