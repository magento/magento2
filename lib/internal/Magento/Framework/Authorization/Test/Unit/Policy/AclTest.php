<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Authorization\Test\Unit\Policy;

class AclTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Authorization\Policy\Acl
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_aclMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_aclBuilderMock;

    protected function setUp()
    {
        $this->_aclMock = $this->getMock('Magento\Framework\Acl');
        $this->_aclBuilderMock = $this->getMock('Magento\Framework\Acl\Builder', [], [], '', false);
        $this->_aclBuilderMock->expects($this->any())->method('getAcl')->will($this->returnValue($this->_aclMock));
        $this->_model = new \Magento\Framework\Authorization\Policy\Acl($this->_aclBuilderMock);
    }

    public function testIsAllowedReturnsTrueIfResourceIsAllowedToRole()
    {
        $this->_aclMock->expects(
            $this->once()
        )->method(
            'isAllowed'
        )->with(
            'some_role',
            'some_resource'
        )->will(
            $this->returnValue(true)
        );

        $this->assertTrue($this->_model->isAllowed('some_role', 'some_resource'));
    }

    public function testIsAllowedReturnsFalseIfRoleDoesntExist()
    {
        $this->_aclMock->expects(
            $this->once()
        )->method(
            'isAllowed'
        )->with(
            'some_role',
            'some_resource'
        )->will(
            $this->throwException(new \Zend_Acl_Role_Registry_Exception())
        );

        $this->_aclMock->expects($this->once())->method('has')->with('some_resource')->will($this->returnValue(true));

        $this->assertFalse($this->_model->isAllowed('some_role', 'some_resource'));
    }

    public function testIsAllowedReturnsTrueIfResourceDoesntExistAndAllResourcesAreNotPermitted()
    {
        $this->_aclMock->expects(
            $this->at(0)
        )->method(
            'isAllowed'
        )->with(
            'some_role',
            'some_resource'
        )->will(
            $this->throwException(new \Zend_Acl_Role_Registry_Exception())
        );

        $this->_aclMock->expects($this->once())->method('has')->with('some_resource')->will($this->returnValue(false));

        $this->_aclMock->expects(
            $this->at(2)
        )->method(
            'isAllowed'
        )->with(
            'some_role',
            null
        )->will(
            $this->returnValue(true)
        );

        $this->assertTrue($this->_model->isAllowed('some_role', 'some_resource'));
    }
}
