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
    private $_model;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $_sessionMock = [];

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->_sessionMock = $this->createPartialMock(
            \Magento\Backend\Model\Auth\Session::class,
            ['getUser', 'getAclRole', 'hasUser']
        );
        $this->_model = new \Magento\Backend\Model\Authorization\RoleLocator($this->_sessionMock);
    }

    public function testGetAclRoleIdReturnsCurrentUserAclRoleId()
    {
        $this->_sessionMock->expects($this->once())->method('hasUser')->willReturn(true);
        $this->_sessionMock->expects($this->once())->method('getUser')->willReturnSelf();
        $this->_sessionMock->expects($this->once())->method('getAclRole')->willReturn('some_role');
        $this->assertEquals('some_role', $this->_model->getAclRoleId());
    }
}
