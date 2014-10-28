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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Authorization\Model\Acl\Loader;

class RoleTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Authorization\Model\Acl\Loader\Role
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_resourceMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_adapterMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_roleFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_groupFactoryMock;

    protected function setUp()
    {
        $this->_resourceMock = $this->getMock('Magento\Framework\App\Resource', array(), array(), '', false, false);
        $this->_groupFactoryMock = $this->getMock(
            'Magento\Authorization\Model\Acl\Role\GroupFactory',
            array('create'),
            array(),
            '',
            false
        );
        $this->_roleFactoryMock = $this->getMock(
            'Magento\Authorization\Model\Acl\Role\UserFactory',
            array('create'),
            array(),
            '',
            false
        );

        $this->_resourceMock->expects(
            $this->once()
        )->method(
            'getTableName'
        )->with(
            $this->equalTo('authorization_role')
        )->will(
            $this->returnArgument(1)
        );


        $selectMock = $this->getMock('Magento\Framework\DB\Select', array(), array(), '', false);
        $selectMock->expects($this->any())->method('from')->will($this->returnValue($selectMock));

        $this->_adapterMock = $this->getMock('Magento\Framework\DB\Adapter\Pdo\Mysql', array(), array(), '', false);
        $this->_adapterMock->expects($this->once())->method('select')->will($this->returnValue($selectMock));

        $this->_resourceMock->expects(
            $this->once()
        )->method(
            'getConnection'
        )->will(
            $this->returnValue($this->_adapterMock)
        );

        $this->_model = new \Magento\Authorization\Model\Acl\Loader\Role(
            $this->_groupFactoryMock,
            $this->_roleFactoryMock,
            $this->_resourceMock
        );
    }

    public function testPopulateAclAddsRolesAndTheirChildren()
    {
        $this->_adapterMock->expects(
            $this->once()
        )->method(
            'fetchAll'
        )->will(
            $this->returnValue(
                array(
                    array('role_id' => 1, 'role_type' => 'G', 'parent_id' => null),
                    array('role_id' => 2, 'role_type' => 'U', 'parent_id' => 1, 'user_id' => 1)
                )
            )
        );


        $this->_groupFactoryMock->expects($this->once())->method('create')->with(array('roleId' => '1'));
        $this->_roleFactoryMock->expects($this->once())->method('create')->with(array('roleId' => '2'));

        $aclMock = $this->getMock('Magento\Framework\Acl');
        $aclMock->expects($this->at(0))->method('addRole')->with($this->anything(), null);
        $aclMock->expects($this->at(2))->method('addRole')->with($this->anything(), '1');

        $this->_model->populateAcl($aclMock);
    }

    public function testPopulateAclAddsMultipleParents()
    {
        $this->_adapterMock->expects(
            $this->once()
        )->method(
            'fetchAll'
        )->will(
            $this->returnValue(array(array('role_id' => 1, 'role_type' => 'U', 'parent_id' => 2, 'user_id' => 3)))
        );

        $this->_roleFactoryMock->expects($this->never())->method('getModelInstance');
        $this->_groupFactoryMock->expects($this->never())->method('getModelInstance');

        $aclMock = $this->getMock('Magento\Framework\Acl');
        $aclMock->expects($this->at(0))->method('hasRole')->with('1')->will($this->returnValue(true));
        $aclMock->expects($this->at(1))->method('addRoleParent')->with('1', '2');

        $this->_model->populateAcl($aclMock);
    }
}
