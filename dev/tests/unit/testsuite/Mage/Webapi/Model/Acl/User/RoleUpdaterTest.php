<?php
/**
 * Test class for Mage_Webapi_Model_Acl_User_RoleUpdater
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
 */
class Mage_Webapi_Model_Acl_User_RoleUpdaterrTest extends PHPUnit_Framework_TestCase
{
    public function testUpdate()
    {
        $userId = 5;
        $expectedRoleId = 3;

        $helper = new Magento_Test_Helper_ObjectManager($this);

        $request = $this->getMockBuilder('Mage_Core_Controller_Request_Http')
            ->disableOriginalConstructor()
            ->getMock();
        $request->expects($this->any())->method('getParam')->will($this->returnValueMap(array(
            array('user_id', null, $userId)
        )));

        $userModel = $this->getMockBuilder('Mage_Webapi_Model_Acl_User')
            ->setMethods(array('getRoleId', 'load'))
            ->disableOriginalConstructor()
            ->getMock();
        $userModel->expects($this->once())->method('load')
            ->with($userId, null)->will($this->returnSelf());
        $userModel->expects($this->once())->method('getRoleId')
            ->with()->will($this->returnValue($expectedRoleId));

        $userFactory = $this->getMockBuilder('Mage_Webapi_Model_Acl_User_Factory')
            ->setMethods(array('create'))
            ->disableOriginalConstructor()
            ->getMock();
        $userFactory->expects($this->once())->method('create')
            ->with(array())->will($this->returnValue($userModel));

        /** @var Mage_Webapi_Model_Acl_Role_InRoleUserUpdater $model */
        $model = $helper->getModel('Mage_Webapi_Model_Acl_User_RoleUpdater', array(
            'request' => $request,
            'userFactory' => $userFactory
        ));

        $this->assertEquals($expectedRoleId, $model->update(array()));
    }
}
