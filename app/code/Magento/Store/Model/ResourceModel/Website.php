<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Store\Model\ResourceModel;

/**
 * Website Resource Model
 */
class Website extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Define main table
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('store_website', 'website_id');
    }

    /**
     * Initialize unique fields
     *
     * @return $this
     */
    protected function _initUniqueFields()
    {
        $this->_uniqueFields = [['field' => 'code', 'title' => __('Website with the same code')]];
        return $this;
    }

    /**
     * Read information about all websites
     *
     * @return array
     */
    public function readAlllWebsites()
    {
        $select = $this->getConnection()
            ->select()
            ->from($this->getTable('store_website'));

        return $this->getConnection()->fetchAll($select);
    }

    /**
     * Validate website code before object save
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
    {
        if (!preg_match('/^[a-z]+[a-z0-9_]*$/', $object->getCode())) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __(
                    'Website code may only contain letters (a-z), numbers (0-9) or underscore (_),'
                    . ' and the first character must be a letter.'
                )
            );
        }

        return parent::_beforeSave($object);
    }

    /**
     * Perform actions after object save
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _afterSave(\Magento\Framework\Model\AbstractModel $object)
    {
        if ($object->getIsDefault()) {
            $this->getConnection()->update($this->getMainTable(), ['is_default' => 0]);
            $where = ['website_id = ?' => $object->getId()];
            $this->getConnection()->update($this->getMainTable(), ['is_default' => 1], $where);
        }
        return parent::_afterSave($object);
    }

    /**
     * Remove configuration data after delete website
     *
     * @param \Magento\Framework\Model\AbstractModel $model
     * @return $this
     */
    protected function _afterDelete(\Magento\Framework\Model\AbstractModel $model)
    {
        $where = [
            'scope = ?' => \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITES,
            'scope_id = ?' => $model->getWebsiteId(),
        ];

        $this->getConnection()->delete($this->getTable('core_config_data'), $where);

        return $this;
    }

    /**
     * Retrieve default stores select object
     * Select fields website_id, store_id
     *
     * @param bool $includeDefault include/exclude default admin website
     * @return \Magento\Framework\DB\Select
     */
    public function getDefaultStoresSelect($includeDefault = false)
    {
        $ifNull = $this->getConnection()->getCheckSql(
            'store_group_table.default_store_id IS NULL',
            '0',
            'store_group_table.default_store_id'
        );
        $select = $this->getConnection()->select()->from(
            ['website_table' => $this->getTable('store_website')],
            ['website_id']
        )->joinLeft(
            ['store_group_table' => $this->getTable('store_group')],
            'website_table.website_id=store_group_table.website_id' .
            ' AND website_table.default_group_id = store_group_table.group_id',
            ['store_id' => $ifNull]
        );
        if (!$includeDefault) {
            $select->where('website_table.website_id <> ?', 0);
        }
        return $select;
    }

    /**
     * Get total number of persistent entities in the system, excluding the admin website by default
     *
     * @param bool $includeDefault
     * @return int
     */
    public function countAll($includeDefault = false)
    {
        $connection = $this->getConnection();
        $select = $connection->select()->from($this->getMainTable(), 'COUNT(*)');
        if (!$includeDefault) {
            $select->where('website_id <> ?', 0);
        }
        return (int)$connection->fetchOne($select);
    }
}
