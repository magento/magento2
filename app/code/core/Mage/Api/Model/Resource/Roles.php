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
 * @package     Mage_Api
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * ACL roles resource
 *
 * @category    Mage
 * @package     Mage_Api
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Api_Model_Resource_Roles extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * User table name
     *
     * @var unknown
     */
    protected $_usersTable;

    /**
     * Rule table name
     *
     * @var unknown
     */
    protected $_ruleTable;

    /**
     * Resource initialization
     *
     */
    protected function _construct()
    {
        $this->_init('api_role', 'role_id');

        $this->_usersTable  = $this->getTable('api_user');
        $this->_ruleTable   = $this->getTable('api_rule');
    }

    /**
     * Action before save
     *
     * @param Mage_Core_Model_Abstract $role
     * @return Mage_Api_Model_Resource_Roles
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
            $row = $this->load($role->getPid());
        } else {
            $row = array('tree_level' => 0);
        }
        $role->setTreeLevel($row['tree_level'] + 1);
        $role->setRoleName($role->getName());
        return $this;
    }

    /**
     * Action after save
     *
     * @param Mage_Core_Model_Abstract $role
     * @return Mage_Api_Model_Resource_Roles
     */
    protected function _afterSave(Mage_Core_Model_Abstract $role)
    {
        $this->_updateRoleUsersAcl($role);
        Mage::app()->getCache()->clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG);
        return $this;
    }

    /**
     * Action after delete
     *
     * @param Mage_Core_Model_Abstract $role
     * @return Mage_Api_Model_Resource_Roles
     */
    protected function _afterDelete(Mage_Core_Model_Abstract $role)
    {
        $adapter = $this->_getWriteAdapter();
        $adapter->delete($this->getMainTable(), array('parent_id=?'=>$role->getId()));
        $adapter->delete($this->_ruleTable, array('role_id=?'=>$role->getId()));
        return $this;
    }

    /**
     * Get role users
     *
     * @param Mage_Api_Model_Roles $role
     * @return unknown
     */
    public function getRoleUsers(Mage_Api_Model_Roles $role)
    {
        $adapter   = $this->_getReadAdapter();
        $select     = $adapter->select()
            ->from($this->getMainTable(), array('user_id'))
            ->where('parent_id = ?', $role->getId())
            ->where('role_type = ?', Mage_Api_Model_Acl::ROLE_TYPE_USER)
            ->where('user_id > 0');
        return $adapter->fetchCol($select);
    }

    /**
     * Update role users
     *
     * @param Mage_Api_Model_Roles $role
     * @return boolean
     */
    private function _updateRoleUsersAcl(Mage_Api_Model_Roles $role)
    {
        $users  = $this->getRoleUsers($role);
        $rowsCount = 0;
        if (sizeof($users) > 0) {
            $rowsCount = $this->_getWriteAdapter()->update(
                $this->_usersTable,
                array('reload_acl_flag' => 1),
                array('user_id IN(?)' => $users));
        }
        return ($rowsCount > 0) ? true : false;
    }
}
