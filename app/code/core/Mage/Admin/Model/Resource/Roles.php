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
 * @category    Mage
 * @package     Mage_Admin
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Admin roles resource model
 *
 * @category    Mage
 * @package     Mage_Admin
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Admin_Model_Resource_Roles extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * Users table
     *
     * @var string
     */
    protected $_usersTable;

    /**
     * Rule table
     *
     * @var string
     */
    protected $_ruleTable;

    /**
     * Define main table
     *
     */
    protected function _construct()
    {
        $this->_init('admin_role', 'role_id');

        $this->_usersTable = $this->getTable('admin_user');
        $this->_ruleTable = $this->getTable('admin_rule');
    }

    /**
     * Process role before saving
     *
     * @param Mage_Core_Model_Abstract $role
     * @return Mage_Admin_Model_Resource_Roles
     */
    protected function _beforeSave(Mage_Core_Model_Abstract $role)
    {
        if ($role->getId() == '') {
            if ($role->getIdFieldName()) {
                $role->unsetData($role->getIdFieldName());
            } else {
                $role->unsetData('id');
            }
        }

        if ($role->getPid() > 0) {
            $select = $this->_getReadAdapter()->select()
                ->from($this->getMainTable(), array('tree_level'))
                ->where("{$this->getIdFieldName()} = :pid");

            $binds = array(
                'pid' => (int) $role->getPid(),
            );

            $treeLevel = $this->_getReadAdapter()->fetchOne($select, $binds);
        } else {
            $treeLevel = 0;
        }
        $role->setTreeLevel($treeLevel + 1);
        $role->setRoleName($role->getName());
        return $this;
    }

    /**
     * Process role after saving
     *
     * @param Mage_Core_Model_Abstract $role
     * @return Mage_Admin_Model_Resource_Roles
     */
    protected function _afterSave(Mage_Core_Model_Abstract $role)
    {
        $this->_updateRoleUsersAcl($role);
        Mage::app()->getCache()->clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG,
            array(Mage_Adminhtml_Block_Page_Menu::CACHE_TAGS));
        return $this;
    }

    /**
     * Process role after deleting
     *
     * @param Mage_Core_Model_Abstract $role
     * @return Mage_Admin_Model_Resource_Roles
     */
    protected function _afterDelete(Mage_Core_Model_Abstract $role)
    {
        $adapter = $this->_getWriteAdapter();

        $adapter->delete(
            $this->getMainTable(),
            array('parent_id = ?' => (int) $role->getId())
        );

        $adapter->delete(
            $this->_ruleTable,
            array('role_id = ?' => (int) $role->getId())
        );

        return $this;
    }

    /**
     * Get role users
     *
     * @param Mage_Admin_Model_Roles $role
     * @return array|false
     */
    public function getRoleUsers(Mage_Admin_Model_Roles $role)
    {
        $read = $this->_getReadAdapter();

        $binds = array(
            'role_id'   => $role->getId(),
            'role_type' => 'U'
        );

        $select = $read->select()
            ->from($this->getMainTable(), array('user_id'))
            ->where('parent_id = :role_id')
            ->where('role_type = :role_type')
            ->where('user_id > 0');

        return $read->fetchCol($select, $binds);
    }

    /**
     * Update role users ACL
     *
     * @param Mage_Admin_Model_Roles $role
     * @return bool
     */
    private function _updateRoleUsersAcl(Mage_Admin_Model_Roles $role)
    {
        $write  = $this->_getWriteAdapter();
        $users  = $this->getRoleUsers($role);
        $rowsCount = 0;

        if (sizeof($users) > 0) {
            $bind  = array('reload_acl_flag' => 1);
            $where = array('user_id IN(?)' => $users);
            $rowsCount = $write->update($this->_usersTable, $bind, $where);
        }

        return $rowsCount > 0;
    }
}
