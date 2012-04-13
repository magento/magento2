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
 * API User ACL model
 *
 * @category   Mage
 * @package    Mage_Api2
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Api2_Model_Acl extends Zend_Acl
{
    /**
     * REST ACL roles collection
     *
     * @var Mage_Api2_Model_Resource_Acl_Global_Role_Collection
     */
    protected $_rolesCollection;

    /**
     * API2 config model instance
     *
     * @var Mage_Api2_Model_Config
     */
    protected $_config;

    /**
     * Resource type of request
     *
     * @var string
     */
    protected $_resourceType;

    /**
     * Operation of request
     *
     * @var string
     */
    protected $_operation;

    /**
     * Constructor
     *
     * @param array $options
     */
    public function __construct($options)
    {
        if (!isset($options['resource_type']) || empty($options['resource_type'])) {
            throw new Exception("Passed parameter 'resource_type' is wrong.");
        }
        if (!isset($options['operation']) || empty($options['operation'])) {
            throw new Exception("Passed parameter 'operation' is wrong.");
        }
        $this->_resourceType = $options['resource_type'];
        $this->_operation = $options['operation'];

        $this->_setResources();
        $this->_setRoles();
        $this->_setRules();
    }

    /**
     * Retrieve REST ACL roles collection
     *
     * @return Mage_Api2_Model_Resource_Acl_Global_Role_Collection
     */
    protected function _getRolesCollection()
    {
        if (null === $this->_rolesCollection) {
            $this->_rolesCollection = Mage::getResourceModel('Mage_Api2_Model_Resource_Acl_Global_Role_Collection');
        }
        return $this->_rolesCollection;
    }

    /**
     * Retrieve API2 config model instance
     *
     * @return Mage_Api2_Model_Config
     */
    protected function _getConfig()
    {
        if (null === $this->_config) {
            $this->_config = Mage::getModel('Mage_Api2_Model_Config');
        }
        return $this->_config;
    }

    /**
     * Retrieve resources types and set into ACL
     *
     * @return Mage_Api2_Model_Acl
     */
    protected function _setResources()
    {
        foreach ($this->_getConfig()->getResourcesTypes() as $type) {
            $this->addResource($type);
        }
        return $this;
    }

    /**
     * Retrieve roles from DB and set into ACL
     *
     * @return Mage_Api2_Model_Acl
     */
    protected function _setRoles()
    {
        /** @var $role Mage_Api2_Model_Acl_Global_Role */
        foreach ($this->_getRolesCollection() as $role) {
            $this->addRole($role->getId());
        }
        return $this;
    }

    /**
     * Retrieve rules data from DB and inject it into ACL
     *
     * @return Mage_Api2_Model_Acl
     */
    protected function _setRules()
    {
        /** @var $rulesCollection Mage_Api2_Model_Resource_Acl_Global_Rule_Collection */
        $rulesCollection = Mage::getResourceModel('Mage_Api2_Model_Resource_Acl_Global_Rule_Collection');

        /** @var $rule Mage_Api2_Model_Acl_Global_Rule */
        foreach ($rulesCollection as $rule) {
            if (Mage_Api2_Model_Acl_Global_Rule::RESOURCE_ALL === $rule->getResourceId()) {
                if (in_array($rule->getRoleId(), Mage_Api2_Model_Acl_Global_Role::getSystemRoles())) {
                    /** @var $role Mage_Api2_Model_Acl_Global_Role */
                    $role = $this->_getRolesCollection()->getItemById($rule->getRoleId());
                    $privileges = $this->_getConfig()->getResourceUserPrivileges(
                        $this->_resourceType,
                        $role->getConfigNodeName()
                    );

                    if (!array_key_exists($this->_operation, $privileges)) {
                        continue;
                    }
                }

                $this->allow($rule->getRoleId());
            } else {
                $this->allow($rule->getRoleId(), $rule->getResourceId(), $rule->getPrivilege());
            }
        }
        return $this;
    }

    /**
     * Adds a Role having an identifier unique to the registry
     * OVERRIDE to allow numeric roles identifiers
     *
     * @param int $roleId Role identifier
     * @param Zend_Acl_Role_Interface|string|array $parents
     * @return Zend_Acl Provides a fluent interface
     */
    public function addRole($roleId, $parents = null)
    {
        if (!is_numeric($roleId)) {
            throw new Exception('Invalid role identifier');
        }
        return parent::addRole((string) $roleId);
    }
}
