<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test class for \Magento\Framework\AuthorizationInterface.
 */
namespace Magento\Framework\Test\Unit;

class AuthorizationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Authorization model
     *
     * @var \Magento\Framework\AuthorizationInterface
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_policyMock;

    protected function setUp()
    {
        $this->_policyMock = $this->getMock('Magento\Framework\Authorization\PolicyInterface');
        $roleLocatorMock = $this->getMock('Magento\Framework\Authorization\RoleLocatorInterface');
        $roleLocatorMock->expects($this->any())->method('getAclRoleId')->will($this->returnValue('U1'));
        $this->_model = new \Magento\Framework\Authorization($this->_policyMock, $roleLocatorMock);
    }

    protected function tearDown()
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
