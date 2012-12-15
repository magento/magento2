<?php
/**
 * Test for Mage_Webapi_Model_Resource_Acl_Rule
 *
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
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @magentoDataFixture Mage/Webapi/_files/role_with_rule.php
 */
class Mage_Webapi_Model_Resource_Acl_RuleTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Magento_Test_ObjectManager
     */
    protected $_objectManager;

    /**
     * @var Mage_Webapi_Model_Resource_Acl_Rule
     */
    protected $_ruleResource;

    protected function setUp()
    {
        $this->_objectManager = Mage::getObjectManager();
        $this->_ruleResource = $this->_objectManager->get('Mage_Webapi_Model_Resource_Acl_Rule');
    }

    protected function tearDown()
    {
        unset($this->_objectManager, $this->_ruleResource);
    }

    /**
     * Test for Mage_Webapi_Model_Resource_Acl_Role::getRolesIds()
     */
    public function testGetRuleList()
    {
        /** @var Mage_Webapi_Model_Acl_Role $role */
        $role = $this->_objectManager->create('Mage_Webapi_Model_Acl_Role')->load('Test role', 'role_name');
        $allowResourceId = 'customer/get';
        $rules = $this->_ruleResource->getRuleList();
        $this->assertCount(1, $rules);
        $this->assertEquals($allowResourceId, $rules[0]['resource_id']);
        $this->assertEquals($role->getId(), $rules[0]['role_id']);
    }

    /**
     * Test for Mage_Webapi_Model_Resource_Acl_Role::getResourceIdsByRole()
     */
    public function testGetResourceIdsByRole()
    {
        /** @var Mage_Webapi_Model_Acl_Role $role */
        $role = $this->_objectManager->create('Mage_Webapi_Model_Acl_Role')->load('Test role', 'role_name');
        $this->assertEquals(array('customer/get'), $this->_ruleResource->getResourceIdsByRole($role->getId()));
    }
}
