<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Backend\Test\Unit\Model\Authorization;

use Magento\Backend\Model\Auth\Session;
use Magento\Backend\Model\Authorization\RoleLocator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class RoleLocatorTest extends TestCase
{
    /**
     * @var RoleLocator
     */
    private $_model;

    /**
     * @var MockObject
     */
    private $_sessionMock = [];

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->_sessionMock = $this->getMockBuilder(Session::class)
            ->addMethods(['getUser', 'getAclRole', 'hasUser'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->_model = new RoleLocator($this->_sessionMock);
    }

    public function testGetAclRoleIdReturnsCurrentUserAclRoleId()
    {
        $this->_sessionMock->expects($this->once())->method('hasUser')->willReturn(true);
        $this->_sessionMock->expects($this->once())->method('getUser')->willReturnSelf();
        $this->_sessionMock->expects($this->once())->method('getAclRole')->willReturn('some_role');
        $this->assertEquals('some_role', $this->_model->getAclRoleId());
    }
}
