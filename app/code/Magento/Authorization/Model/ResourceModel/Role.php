<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Authorization\Model\ResourceModel;

use Magento\Authorization\Model\Acl\Role\User as RoleUser;

/**
 * Admin role resource model
 */
class Role extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Rule table
     *
     * @var string
     */
    protected $_ruleTable;

    /**
     * Cache
     *
     * @var \Magento\Framework\Cache\FrontendInterface
     */
    protected $_cache;

    /**
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Framework\App\CacheInterface $cache
     * @param string $connectionName
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Framework\App\CacheInterface $cache,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
        $this->_cache = $cache->getFrontend();
    }

    /**
     * Define main table
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('authorization_role', 'role_id');
        $this->_ruleTable = $this->getTable('authorization_rule');
    }

    /**
     * Process role before saving
     *
     * @param \Magento\Framework\Model\AbstractModel $role
     * @return $this
     */
    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $role)
    {
        if ($role->getId() == '') {
            if ($role->getIdFieldName()) {
                $role->unsetData($role->getIdFieldName());
            } else {
                $role->unsetData('id');
            }
        }

        if (!$role->getTreeLevel()) {
            $treeLevel = 0;
            if ($role->getPid() > 0) {
                $select = $this->getConnection()->select()->from(
                    $this->getMainTable(),
                    ['tree_level']
                )->where(
                    "{$this->getIdFieldName()} = :pid"
                );

                $binds = ['pid' => (int)$role->getPid()];

                $treeLevel = $this->getConnection()->fetchOne($select, $binds);
            }

            $role->setTreeLevel($treeLevel + 1);
        }

        if ($role->getName()) {
            $role->setRoleName($role->getName());
        }

        return $this;
    }

    /**
     * Process role after saving
     *
     * @param \Magento\Framework\Model\AbstractModel $role
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _afterSave(\Magento\Framework\Model\AbstractModel $role)
    {
        $this->_cache->clean(\Zend_Cache::CLEANING_MODE_MATCHING_TAG, [\Magento\Backend\Block\Menu::CACHE_TAGS]);
        return $this;
    }

    /**
     * Process role after deleting
     *
     * @param \Magento\Framework\Model\AbstractModel $role
     * @return $this
     */
    protected function _afterDelete(\Magento\Framework\Model\AbstractModel $role)
    {
        $connection = $this->getConnection();

        $connection->delete($this->getMainTable(), ['parent_id = ?' => (int)$role->getId()]);

        $connection->delete($this->_ruleTable, ['role_id = ?' => (int)$role->getId()]);

        return $this;
    }

    /**
     * Get role users
     *
     * @param \Magento\Authorization\Model\Role $role
     * @return array
     */
    public function getRoleUsers(\Magento\Authorization\Model\Role $role)
    {
        $connection = $this->getConnection();

        $binds = ['role_id' => $role->getId(), 'role_type' => RoleUser::ROLE_TYPE];

        $select = $connection->select()
            ->from($this->getMainTable(), ['user_id'])
            ->where('parent_id = :role_id')
            ->where('role_type = :role_type')
            ->where('user_id > 0');

        return $connection->fetchCol($select, $binds);
    }
}
