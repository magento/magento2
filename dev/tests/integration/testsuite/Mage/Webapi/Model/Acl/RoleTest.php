<?php
/**
 * Test for Mage_Webapi_Model_Acl_Role model
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
 * @magentoDataFixture Mage/Webapi/_files/role.php
 */
class Mage_Webapi_Model_Acl_RoleTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Magento_Test_ObjectManager
     */
    protected $_objectManager;

    /**
     * @var Mage_Webapi_Model_Acl_Role
     */
    protected $_model;

    /**
     * Initialize model
     */
    protected function setUp()
    {
        $this->_objectManager = Mage::getObjectManager();
        $this->_model = $this->_objectManager->create('Mage_Webapi_Model_Acl_Role');
    }

    /**
     * Cleanup model instance
     */
    protected function tearDown()
    {
        unset($this->_objectManager, $this->_model);
    }

    /**
     * Test Web API Role CRUD
     */
    public function testCRUD()
    {
        $this->_model->setRoleName('Test Role Name');
        $crud = new Magento_Test_Entity($this->_model, array('role_name' => '_Role_Name_'));
        $crud->testCrud();
    }
}
