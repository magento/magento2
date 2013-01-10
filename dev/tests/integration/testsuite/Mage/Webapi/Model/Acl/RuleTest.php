<?php
/**
 * Test for Mage_Webapi_Model_Acl_Rule model
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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @magentoDataFixture Mage/Webapi/_files/role.php
 */
class Mage_Webapi_Model_Acl_RuleTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Magento_Test_ObjectManager
     */
    protected $_objectManager;

    /**
     * @var Mage_Webapi_Model_Acl_Role_Factory
     */
    protected $_roleFactory;

    /**
     * @var Mage_Webapi_Model_Acl_Rule
     */
    protected $_model;

    protected function setUp()
    {
        $this->_objectManager = Mage::getObjectManager();
        $this->_roleFactory = $this->_objectManager->get('Mage_Webapi_Model_Acl_Role_Factory');
        $this->_model = $this->_objectManager->create('Mage_Webapi_Model_Acl_Rule');
    }

    /**
     * Cleanup model instance
     */
    protected function tearDown()
    {
        unset($this->_objectManager, $this->_model);
    }

    /**
     * Test Web API Rule CRUD
     */
    public function testCRUD()
    {
        $role = $this->_roleFactory->create()->load('test_role', 'role_name');
        $allowResourceId = 'customer/multiGet';

        $this->_model->setRoleId($role->getId())
            ->setResourceId($allowResourceId);

        $crud = new Magento_Test_Entity($this->_model, array('resource_id' => 'customer/get'));
        $crud->testCrud();
    }

    /**
     * Test method Mage_Webapi_Model_Acl_Rule::saveResources()
     */
    public function testSaveResources()
    {
        $role = $this->_roleFactory->create()->load('test_role', 'role_name');
        $resources = array('customer/create', 'customer/update');

        $this->_model
            ->setRoleId($role->getId())
            ->setResources($resources)
            ->saveResources();

        /** @var $rulesSet Mage_Webapi_Model_Resource_Acl_Rule_Collection */
        $rulesSet = $this->_objectManager->get('Mage_Webapi_Model_Resource_Acl_Rule_Collection')
            ->getByRole($role->getRoleId())->load();
        $this->assertCount(2, $rulesSet);
    }
}
