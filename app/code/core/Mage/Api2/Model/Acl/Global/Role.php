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
 * @package     Mage_Api2
 * @copyright  Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * API2 Global ACL Role model
 *
 * @category    Mage
 * @package     Mage_Api2
 * @author      Magento Core Team <core@magentocommerce.com>
 * @method Mage_Api2_Model_Resource_Acl_Global_Role_Collection getCollection()
 * @method Mage_Api2_Model_Resource_Acl_Global_Role_Collection getResourceCollection()
 * @method Mage_Api2_Model_Resource_Acl_Global_Role getResource()
 * @method Mage_Api2_Model_Resource_Acl_Global_Role _getResource()
 * @method string getCreatedAt()
 * @method Mage_Api2_Model_Acl_Global_Role setCreatedAt() setCreatedAt(string $createdAt)
 * @method string getUpdatedAt()
 * @method Mage_Api2_Model_Acl_Global_Role setUpdatedAt() setUpdatedAt(string $updatedAt)
 * @method string getRoleName()
 * @method Mage_Api2_Model_Acl_Global_Role setRoleName() setRoleName(string $roleName)
 */
class Mage_Api2_Model_Acl_Global_Role extends Mage_Core_Model_Abstract
{
    /**#@+
     * System roles identifiers
     */
    const ROLE_GUEST_ID = 1;
    const ROLE_CUSTOMER_ID = 2;
    /**#@-*/

    /**#@+
     * Config node identifiers
     */
    const ROLE_CONFIG_NODE_NAME_GUEST = 'guest';
    const ROLE_CONFIG_NODE_NAME_CUSTOMER = 'customer';
    const ROLE_CONFIG_NODE_NAME_ADMIN = 'admin';
    /**#@-*/

    /**
     * Permissions model
     *
     * @var Mage_Api2_Model_Acl_Global_Rule_ResourcePermission
     */
    protected $_permissionModel;

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Mage_Api2_Model_Resource_Acl_Global_Role');
    }

    /**
     * Before save actions
     *
     * @return Mage_Api2_Model_Acl_Global_Role
     */
    protected function _beforeSave()
    {
        if ($this->isObjectNew() && null === $this->getCreatedAt()) {
            $this->setCreatedAt(Varien_Date::now());
        } else {
            $this->setUpdatedAt(Varien_Date::now());
        }

        //check and protect guest role
        if (Mage_Api2_Model_Acl_Global_Role::isSystemRole($this)
            && $this->getRoleName() != $this->getOrigData('role_name')) {

            /** @var $helper Mage_Core_Helper_Data */
            $helper = Mage::helper('Mage_Core_Helper_Data');

            Mage::throwException(
                Mage::helper('Mage_Api2_Helper_Data')->__('%s role is a special one and can\'t be changed.',
                    $helper->escapeHtml($this->getRoleName()))
            );
        }

        parent::_beforeSave();
        return $this;
    }

    /**
     * Perform checks before role delete
     *
     * @return Mage_Api2_Model_Acl_Global_Role
     */
    protected function _beforeDelete()
    {
        if (Mage_Api2_Model_Acl_Global_Role::isSystemRole($this)) {
            /** @var $helper Mage_Core_Helper_Data */
            $helper = Mage::helper('Mage_Core_Helper_Data');

            Mage::throwException(
                Mage::helper('Mage_Api2_Helper_Data')->__('%s role is a special one and can\'t be deleted.',
                    $helper->escapeHtml($this->getRoleName()))
            );
        }

        parent::_beforeDelete();
        return $this;
    }

    /**
     * Get pairs resources-permissions for current role
     *
     * @return Mage_Api2_Model_Acl_Global_Rule_ResourcePermission
     */
    public function getPermissionModel()
    {
        if (null == $this->_permissionModel) {
            $this->_permissionModel = Mage::getModel('Mage_Api2_Model_Acl_Global_Rule_ResourcePermission');
        }
        return $this->_permissionModel;
    }

    /**
     * Retrieve system roles
     *
     * @return array
     */
    static public function getSystemRoles()
    {
        return array(
            self::ROLE_GUEST_ID,
            self::ROLE_CUSTOMER_ID
        );
    }

    /**
     * Get role system belonging
     *
     * @param Mage_Api2_Model_Acl_Global_Role $role
     * @return bool
     */
    public static function isSystemRole($role)
    {
        return in_array($role->getId(), self::getSystemRoles());
    }

    /**
     * Get config node identifiers
     *
     * @return string
     */
    public function getConfigNodeName()
    {
        switch ($this->getId()) {
            case self::ROLE_GUEST_ID:
                $roleNodeName = self::ROLE_CONFIG_NODE_NAME_GUEST;
                break;
            case self::ROLE_CUSTOMER_ID:
                $roleNodeName = self::ROLE_CONFIG_NODE_NAME_CUSTOMER;
                break;
            default:
                $roleNodeName = self::ROLE_CONFIG_NODE_NAME_ADMIN;
        }
        return $roleNodeName;
    }
}
