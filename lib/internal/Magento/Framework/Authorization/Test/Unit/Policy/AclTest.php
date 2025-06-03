<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Authorization\Test\Unit\Policy;

use Laminas\Permissions\Acl\Exception\InvalidArgumentException;
use Magento\Framework\Acl\Builder;
use Magento\Framework\Authorization\Policy\Acl;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Framework\Acl as FrameworkAcl;

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

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->_aclMock = $this->createMock(FrameworkAcl::class);
        $this->_aclBuilderMock = $this->createMock(Builder::class);
        $this->_aclBuilderMock->expects($this->any())->method('getAcl')->willReturn($this->_aclMock);
        $this->_model = new Acl($this->_aclBuilderMock);
    }

    /**
     * @return void
     */
    public function testIsAllowedReturnsTrueIfResourceIsAllowedToRole(): void
    {
        $this->_aclMock->expects($this->once())
            ->method('isAllowed')
            ->with('some_role', 'some_resource')
            ->willReturn(true);

        $this->assertTrue($this->_model->isAllowed('some_role', 'some_resource'));
    }

    /**
     * @return void
     */
    public function testIsAllowedReturnsFalseIfRoleDoesntExist(): void
    {
        $this->_aclMock->expects($this->once())
        ->method('isAllowed')
            ->with('some_role', 'some_resource')
            ->willThrowException(new InvalidArgumentException());

        $this->_aclMock->expects($this->once())->method('hasResource')->with('some_resource')->willReturn(true);
        $this->assertFalse($this->_model->isAllowed('some_role', 'some_resource'));
    }

    /**
     * @return void
     */
    public function testIsAllowedReturnsTrueIfResourceDoesntExistAndAllResourcesAreNotPermitted(): void
    {
        $this->_aclMock
            ->method('isAllowed')
            ->willReturnCallback(
                function ($arg1, $arg2) {
                    if ($arg1 == 'some_role' && $arg2 == 'some_resource') {
                        throw new InvalidArgumentException();
                    } elseif ($arg1 == 'some_role' && $arg2 == null) {
                        return true;
                    }
                }
            );

        $this->_aclMock->expects($this->once())->method('hasResource')->with('some_resource')->willReturn(false);
        $this->assertTrue($this->_model->isAllowed('some_role', 'some_resource'));
    }
}
