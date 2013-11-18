<?php
namespace Magento\Webapi\Model\Acl;

/**
 * Test for \Magento\Webapi\Model\Acl\User model.
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
 * @magentoDataFixture Magento/Webapi/_files/role.php
 */
class UserTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\TestFramework\ObjectManager
     */
    protected $_objectManager;

    /**
     * @var \Magento\Webapi\Model\Acl\User
     */
    protected $_model;

    /**
     * @var \Magento\Webapi\Model\Acl\Role\Factory
     */
    protected $_roleFactory;

    /**
     * Initialize model.
     */
    protected function setUp()
    {
        $this->_objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->_roleFactory = $this->_objectManager->get('Magento\Webapi\Model\Acl\Role\Factory');
        $this->_model = $this->_objectManager->create('Magento\Webapi\Model\Acl\User');
    }

    /**
     * Test Web API User CRUD.
     */
    public function testCRUD()
    {
        $role = $this->_roleFactory->create()->load('test_role', 'role_name');

        $this->_model
            ->setApiKey('Test User Name')
            ->setContactEmail('null@null.com')
            ->setSecret('null@null.com')
            ->setRoleId($role->getId());

        $crud = new \Magento\TestFramework\Entity($this->_model, array('api_key' => '_User_Name_'));
        $crud->testCrud();
    }
}
