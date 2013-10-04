<?php
/**
 * Test class for \Magento\Webapi\Model\Authorization\Loader\Role
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
 */
namespace Magento\Webapi\Model\Authorization\Loader;

class RoleTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Webapi\Model\Resource\Acl\Role
     */
    protected $_resourceModelMock;

    /**
     * @var \Magento\Webapi\Model\Authorization\Loader\Role
     */
    protected $_model;

    /**
     * @var \Magento\Webapi\Model\Authorization\Role\Factory
     */
    protected $_roleFactory;

    /**
     * @var \Magento\Acl
     */
    protected $_acl;

    /**
     * Set up before test.
     */
    protected function setUp()
    {
        $helper = new \Magento\TestFramework\Helper\ObjectManager($this);

        $this->_resourceModelMock = $this->getMock('Magento\Webapi\Model\Resource\Acl\Role',
            array('getRolesIds'), array(), '', false);

        $this->_roleFactory = $this->getMock('Magento\Webapi\Model\Authorization\Role\Factory',
            array('createRole'), array(), '', false);

        $this->_acl = $this->getMock('Magento\Acl', array('addRole', 'deny'), array(), '',
            false);

        $this->_model = $helper->getObject('Magento\Webapi\Model\Authorization\Loader\Role', array(
            'roleResource' => $this->_resourceModelMock,
            'roleFactory' => $this->_roleFactory,
        ));
    }

    /**
     * Test for \Magento\Webapi\Model\Authorization\Loader\Role::populateAcl.
     *
     * Test with existing role IDs.
     */
    public function testPopulateAclWithRoles()
    {
        $roleOne = new \Magento\Webapi\Model\Authorization\Role(3);
        $roleTwo = new \Magento\Webapi\Model\Authorization\Role(4);
        $roleIds = array(3, 4);
        $createRoleMap = array(
            array(array('roleId' => 3), $roleOne),
            array(array('roleId' => 4), $roleTwo),
        );
        $this->_resourceModelMock->expects($this->once())
            ->method('getRolesIds')
            ->will($this->returnValue($roleIds));

        $this->_roleFactory->expects($this->exactly(count($roleIds)))
            ->method('createRole')
            ->will($this->returnValueMap($createRoleMap));

        $this->_acl->expects($this->exactly(count($roleIds)))
            ->method('addRole')
            ->with($this->logicalOr($roleOne, $roleTwo));

        $this->_acl->expects($this->exactly(count($roleIds)))
            ->method('deny')
            ->with($this->logicalOr($roleOne, $roleTwo));

        $this->_model->populateAcl($this->_acl);
    }

    /**
     * Test for \Magento\Webapi\Model\Authorization\Loader\Role::populateAcl.
     *
     * Test with No existing role IDs.
     */
    public function testPopulateAclWithNoRoles()
    {
        $this->_resourceModelMock->expects($this->once())
            ->method('getRolesIds')
            ->will($this->returnValue(array()));

        $this->_roleFactory->expects($this->never())
            ->method('createRole');

        $this->_acl->expects($this->never())
            ->method('addRole');

        $this->_acl->expects($this->never())
            ->method('deny');

        $this->_model->populateAcl($this->_acl);
    }
}
