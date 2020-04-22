<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
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
        $this->_sessionMock = $this->createPartialMock(
            Session::class,
            ['getUser', 'getAclRole', 'hasUser']
        );
        $this->_model = new RoleLocator($this->_sessionMock);
    }

    public function testGetAclRoleIdReturnsCurrentUserAclRoleId()
    {
        $this->_sessionMock->expects($this->once())->method('hasUser')->will($this->returnValue(true));
        $this->_sessionMock->expects($this->once())->method('getUser')->will($this->returnSelf());
        $this->_sessionMock->expects($this->once())->method('getAclRole')->will($this->returnValue('some_role'));
        $this->assertEquals('some_role', $this->_model->getAclRoleId());
    }
}
