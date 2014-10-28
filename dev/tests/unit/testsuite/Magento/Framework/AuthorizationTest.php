<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Test class for \Magento\Framework\AuthorizationInterface.
 */
namespace Magento\Framework;

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
        $this->_policyMock = $this->getMock('Magento\Framework\Authorization\Policy', array(), array(), '', false);
        $roleLocatorMock = $this->getMock('Magento\Framework\Authorization\RoleLocator', array(), array(), '', false);
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
