<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Test\Unit\Model\Authorization;

class RoleLocatorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Backend\Model\Authorization\RoleLocator
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_sessionMock = [];

    protected function setUp()
    {
        $this->_sessionMock = $this->createPartialMock(
            \Magento\Backend\Model\Auth\Session::class,
            ['getUser', 'getAclRole', 'hasUser']
        );
        $this->_model = new \Magento\Backend\Model\Authorization\RoleLocator($this->_sessionMock);
    }

    public function testGetAclRoleIdReturnsCurrentUserAclRoleId()
    {
        $this->_sessionMock->expects($this->once())->method('hasUser')->will($this->returnValue(true));
        $this->_sessionMock->expects($this->once())->method('getUser')->will($this->returnSelf());
        $this->_sessionMock->expects($this->once())->method('getAclRole')->will($this->returnValue('some_role'));
        $this->assertEquals('some_role', $this->_model->getAclRoleId());
    }
}
