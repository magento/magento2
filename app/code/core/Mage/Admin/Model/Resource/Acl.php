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
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Resource model for admin ACL
 *
 * @category    Mage
 * @package     Mage_Admin
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Admin_Model_Resource_Acl extends Mage_Core_Model_Resource_Db_Abstract
{
    const ACL_ALL_RULES = 'all';

    /**
     * Initialize resource
     *
     */
    protected function _construct()
    {
        $this->_init('admin_role', 'role_id');
    }

    /**
     * Load ACL for the user
     *
     * @return Mage_Admin_Model_Acl
     */
    public function loadAcl()
    {
        $acl = Mage::getModel('Mage_Admin_Model_Acl');

        Mage::getSingleton('Mage_Admin_Model_Config')->loadAclResources($acl);

        $roleTable   = $this->getTable('admin_role');
        $ruleTable   = $this->getTable('admin_rule');
        $assertTable = $this->getTable('admin_assert');

        $adapter = $this->_getReadAdapter();

        $select = $adapter->select()
            ->from($roleTable)
            ->order('tree_level');

        $rolesArr = $adapter->fetchAll($select);

        $this->loadRoles($acl, $rolesArr);

        $select = $adapter->select()
            ->from(array('r' => $ruleTable))
            ->joinLeft(
                array('a' => $assertTable),
                'a.assert_id = r.assert_id',
                array('assert_type', 'assert_data')
            );

        $rulesArr = $adapter->fetchAll($select);

        $this->loadRules($acl, $rulesArr);

        return $acl;
    }

    /**
     * Load roles
     *
     * @param Mage_Admin_Model_Acl $acl
     * @param array $rolesArr
     * @return Mage_Admin_Model_Resource_Acl
     */
    public function loadRoles(Mage_Admin_Model_Acl $acl, array $rolesArr)
    {
        foreach ($rolesArr as $role) {
            $parent = ($role['parent_id'] > 0) ? Mage_Admin_Model_Acl::ROLE_TYPE_GROUP . $role['parent_id'] : null;
            switch ($role['role_type']) {
                case Mage_Admin_Model_Acl::ROLE_TYPE_GROUP:
                    $roleId = $role['role_type'] . $role['role_id'];
                    $acl->addRole(Mage::getModel('Mage_Admin_Model_Acl_Role_Group', $roleId), $parent);
                    break;

                case Mage_Admin_Model_Acl::ROLE_TYPE_USER:
                    $roleId = $role['role_type'] . $role['user_id'];
                    if (!$acl->hasRole($roleId)) {
                        $acl->addRole(Mage::getModel('Mage_Admin_Model_Acl_Role_User', $roleId), $parent);
                    } else {
                        $acl->addRoleParent($roleId, $parent);
                    }
                    break;
            }
        }

        return $this;
    }

    /**
     * Load rules
     *
     * @param Mage_Admin_Model_Acl $acl
     * @param array $rulesArr
     * @return Mage_Admin_Model_Resource_Acl
     */
    public function loadRules(Mage_Admin_Model_Acl $acl, array $rulesArr)
    {
        foreach ($rulesArr as $rule) {
            $role = $rule['role_type'] . $rule['role_id'];
            $resource = $rule['resource_id'];
            $privileges = !empty($rule['privileges']) ? explode(',', $rule['privileges']) : null;

            $assert = null;
            if (0 != $rule['assert_id']) {
                $assertClass = Mage::getSingleton('Mage_Admin_Model_Config')->getAclAssert($rule['assert_type'])->getClassName();
                $assert = new $assertClass(unserialize($rule['assert_data']));
            }
            try {
                if ( $rule['permission'] == 'allow' ) {
                    if ($resource === self::ACL_ALL_RULES) {
                        $acl->allow($role, null, $privileges, $assert);
                    }
                    $acl->allow($role, $resource, $privileges, $assert);
                } else if ( $rule['permission'] == 'deny' ) {
                    $acl->deny($role, $resource, $privileges, $assert);
                }
            } catch (Exception $e) {
                //$m = $e->getMessage();
                //if ( eregi("^Resource '(.*)' not found", $m) ) {
                    // Deleting non existent resource rule from rules table
                    //$cond = $this->_write->quoteInto('resource_id = ?', $resource);
                    //$this->_write->delete(Mage::getSingleton('Mage_Core_Model_Resource')->getTableName('admin_rule'), $cond);
                //} else {
                    //TODO: We need to log such exceptions to somewhere like a system/errors.log
                //}
            }
            /*
            switch ($rule['permission']) {
                case Mage_Admin_Model_Acl::RULE_PERM_ALLOW:
                    $acl->allow($role, $resource, $privileges, $assert);
                    break;

                case Mage_Admin_Model_Acl::RULE_PERM_DENY:
                    $acl->deny($role, $resource, $privileges, $assert);
                    break;
            }
            */
        }
        return $this;
    }
}
