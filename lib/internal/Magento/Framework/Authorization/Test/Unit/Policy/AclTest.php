<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Authorization\Test\Unit\Policy;

use Magento\Framework\Acl\Builder;
use Magento\Framework\Authorization\Policy\Acl;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AclTest extends TestCase
{
    /**
     * @var Acl
     */
    protected $_model;

    /**
     * @var MockObject
     */
    protected $_aclMock;

    /**
     * @var MockObject
     */
    protected $_aclBuilderMock;

    protected function setUp(): void
    {
        $this->_aclMock = $this->createMock(\Magento\Framework\Acl::class);
        $this->_aclBuilderMock = $this->createMock(Builder::class);
        $this->_aclBuilderMock->expects($this->any())->method('getAcl')->will($this->returnValue($this->_aclMock));
        $this->_model = new Acl($this->_aclBuilderMock);
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
