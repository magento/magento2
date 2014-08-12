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

namespace Magento\Authorization\Model\Acl;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Authorization\Model\Role;
use Magento\Authorization\Model\Resource\Role\Collection as RoleCollection;
use Magento\Authorization\Model\Resource\Role\CollectionFactory as RoleCollectionFactory;
use Magento\Authorization\Model\Resource\Rules\Collection as RulesCollection;
use Magento\Authorization\Model\Resource\Rules\CollectionFactory as RulesCollectionFactory;

class AclRetrieverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AclRetriever
     */
    protected $aclRetriever;

    /** @var \PHPUnit_Framework_MockObject_MockObject|Role $roleMock */
    protected $roleMock;

    protected function setup()
    {
        $this->aclRetriever = $this->createAclRetriever();
    }

    public function testGetAllowedResourcesByUserTypeGuest()
    {
        $expectedResources = ['anonymous'];
        $allowedResources = $this->aclRetriever->getAllowedResourcesByUser(UserContextInterface::USER_TYPE_GUEST, null);
        $this->assertEquals(
            $expectedResources,
            $allowedResources,
            'Allowed resources for guests should be \'anonymous\'.'
        );
    }

    public function testGetAllowedResourcesByUserTypeCustomer()
    {
        $expectedResources = ['self'];
        $allowedResources = $this->aclRetriever->getAllowedResourcesByUser(
            UserContextInterface::USER_TYPE_CUSTOMER,
            null
        );
        $this->assertEquals(
            $expectedResources,
            $allowedResources,
            'Allowed resources for customers should be \'self\'.'
        );

    }

    /**
     * @expectedException \Magento\Framework\Exception\AuthorizationException
     * @expectedExceptionMessage The role associated with the specified user cannot be found.
     */
    public function testGetAllowedResourcesByUserRoleNotFound()
    {
        $this->roleMock->expects($this->once())->method('getId')->will($this->returnValue(null));
        $this->aclRetriever->getAllowedResourcesByUser(UserContextInterface::USER_TYPE_INTEGRATION, null);
    }

    public function testGetAllowedResourcesByUser()
    {
        $this->roleMock->expects($this->any())->method('getId')->will($this->returnValue(1));
        $expectedResources = ['Magento_Adminhtml::dashboard', 'Magento_Cms::page'];
        $this->assertEquals(
            $expectedResources,
            $this->aclRetriever->getAllowedResourcesByUser(UserContextInterface::USER_TYPE_INTEGRATION, 1)
        );
    }

    protected function createAclRetriever()
    {
        $this->roleMock = $this->getMock(
            'Magento\Authorization\Model\Role',
            array('getId', '__wakeup'),
            array(),
            '',
            false
        );

        /** @var \PHPUnit_Framework_MockObject_MockObject|RoleCollection $roleCollectionMock */
        $roleCollectionMock = $this->getMock(
            'Magento\Authorization\Model\Resource\Role\Collection',
            array('setUserFilter', 'getFirstItem'),
            array(),
            '',
            false
        );
        $roleCollectionMock->expects($this->any())->method('setUserFilter')->will($this->returnSelf());
        $roleCollectionMock->expects($this->any())->method('getFirstItem')->will($this->returnValue($this->roleMock));

        /** @var \PHPUnit_Framework_MockObject_MockObject|RoleCollectionFactory $roleCollectionFactoryMock */
        $roleCollectionFactoryMock = $this->getMock(
            'Magento\Authorization\Model\Resource\Role\CollectionFactory',
            array('create'),
            array(),
            '',
            false
        );
        $roleCollectionFactoryMock->expects($this->any())->method('create')->will(
            $this->returnValue($roleCollectionMock)
        );

        /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Authorization\Model\Rules $rulesMock1 */
        $rulesMock1 = $this->getMock(
            'Magento\Authorization\Model\Rules',
            array('getResourceId', '__wakeup'),
            array(),
            '',
            false
        );
        $rulesMock1->expects($this->any())->method('getResourceId')->will(
            $this->returnValue('Magento_Adminhtml::dashboard')
        );
        /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Authorization\Model\Rules $rulesMock1 */
        $rulesMock2 = $this->getMock(
            'Magento\Authorization\Model\Rules',
            array('getResourceId', '__wakeup'),
            array(),
            '',
            false
        );
        $rulesMock2->expects($this->any())->method('getResourceId')->will($this->returnValue('Magento_Cms::page'));

        /** @var \PHPUnit_Framework_MockObject_MockObject|RulesCollection $rulesCollectionMock */
        $rulesCollectionMock = $this->getMock(
            'Magento\Authorization\Model\Resource\Rules\Collection',
            array('getByRoles', 'load', 'getItems'),
            array(),
            '',
            false
        );
        $rulesCollectionMock->expects($this->any())->method('getByRoles')->will($this->returnSelf());
        $rulesCollectionMock->expects($this->any())->method('load')->will($this->returnSelf());
        $rulesCollectionMock->expects($this->any())->method('getItems')->will(
            $this->returnValue([$rulesMock1, $rulesMock2])
        );

        /** @var \PHPUnit_Framework_MockObject_MockObject|RulesCollectionFactory $rulesCollectionFactoryMock */
        $rulesCollectionFactoryMock = $this->getMock(
            'Magento\Authorization\Model\Resource\Rules\CollectionFactory',
            array('create'),
            array(),
            '',
            false
        );
        $rulesCollectionFactoryMock->expects($this->any())->method('create')->will(
            $this->returnValue($rulesCollectionMock)
        );

        /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Acl $aclMock */
        $aclMock = $this->getMock('Magento\Framework\Acl', array('has', 'isAllowed'), array(), '', false);
        $aclMock->expects($this->any())->method('has')->will($this->returnValue(true));
        $aclMock->expects($this->any())->method('isAllowed')->will($this->returnValue(true));

        /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Acl\Builder $aclBuilderMock */
        $aclBuilderMock = $this->getMock('Magento\Framework\Acl\Builder', array('getAcl'), array(), '', false);
        $aclBuilderMock->expects($this->any())->method('getAcl')->will($this->returnValue($aclMock));

        return new AclRetriever(
            $aclBuilderMock,
            $roleCollectionFactoryMock,
            $rulesCollectionFactoryMock,
            $this->getMock('Magento\Framework\Logger', array(), array(), '', false)
        );
    }
}
