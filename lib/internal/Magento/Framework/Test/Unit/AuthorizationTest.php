<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test class for \Magento\Framework\AuthorizationInterface.
 */
namespace Magento\Framework\Test\Unit;

use Magento\Framework\Authorization;
use Magento\Framework\Authorization\PolicyInterface;
use Magento\Framework\Authorization\RoleLocatorInterface;
use Magento\Framework\AuthorizationInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AuthorizationTest extends TestCase
{
    /**
     * Authorization model
     *
     * @var AuthorizationInterface
     */
    protected $_model;

    /**
     * @var MockObject
     */
    protected $_policyMock;

    protected function setUp(): void
    {
        $this->_policyMock = $this->createMock(PolicyInterface::class);
        $roleLocatorMock = $this->createMock(RoleLocatorInterface::class);
        $roleLocatorMock->expects($this->any())->method('getAclRoleId')->will($this->returnValue('U1'));
        $this->_model = new Authorization($this->_policyMock, $roleLocatorMock);
    }

    protected function tearDown(): void
    {
        unset($this->_model);
    }

    public function testIsAllowedReturnPositiveValue()
    {
        $this->_policyMock->expects($this->once())->method('isAllowed')->will($this->returnValue(true));
        $this->assertTrue($this->_model->isAllowed('Magento_Module::acl_resource'));
    }

    public function testIsAllowedReturnNegativeValue()
    {
        $this->_policyMock->expects($this->once())->method('isAllowed')->will($this->returnValue(false));
        $this->assertFalse($this->_model->isAllowed('Magento_Module::acl_resource'));
    }
}
