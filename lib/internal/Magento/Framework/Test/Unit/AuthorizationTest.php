<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test class for \Magento\Framework\AuthorizationInterface.
 */
namespace Magento\Framework\Test\Unit;

class AuthorizationTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Authorization model
     *
     * @var \Magento\Framework\AuthorizationInterface
     */
    protected $_model;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_policyMock;

    protected function setUp(): void
    {
        $this->_policyMock = $this->createMock(\Magento\Framework\Authorization\PolicyInterface::class);
        $roleLocatorMock = $this->createMock(\Magento\Framework\Authorization\RoleLocatorInterface::class);
        $roleLocatorMock->expects($this->any())->method('getAclRoleId')->willReturn('U1');
        $this->_model = new \Magento\Framework\Authorization($this->_policyMock, $roleLocatorMock);
    }

    protected function tearDown(): void
    {
        unset($this->_model);
    }

    public function testIsAllowedReturnPositiveValue()
    {
        $this->_policyMock->expects($this->once())->method('isAllowed')->willReturn(true);
        $this->assertTrue($this->_model->isAllowed('Magento_Module::acl_resource'));
    }

    public function testIsAllowedReturnNegativeValue()
    {
        $this->_policyMock->expects($this->once())->method('isAllowed')->willReturn(false);
        $this->assertFalse($this->_model->isAllowed('Magento_Module::acl_resource'));
    }
}
