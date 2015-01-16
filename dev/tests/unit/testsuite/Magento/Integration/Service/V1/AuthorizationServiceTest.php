<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Integration\Service\V1;

use Magento\Authorization\Model\Role;
use Magento\Authorization\Model\UserContextInterface;

class AuthorizationServiceTest extends \PHPUnit_Framework_TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject|Role */
    protected $roleMock;

    /** @var AuthorizationService */
    protected $integrationAuthorizationService;

    protected function setUp()
    {
        $this->roleMock = $this->getMock(
            'Magento\Authorization\Model\Role',
            ['load', 'delete', '__wakeup'],
            [],
            '',
            false
        );
        $this->roleMock->expects($this->any())->method('load')->will($this->returnSelf());
        $this->roleMock->expects($this->any())->method('delete')->will($this->returnSelf());

        /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Authorization\Model\RoleFactory $roleFactoryMock */
        $roleFactoryMock = $this->getMock(
            'Magento\Authorization\Model\RoleFactory',
            ['create'],
            [],
            '',
            false
        );
        $roleFactoryMock->expects($this->any())->method('create')->will($this->returnValue($this->roleMock));

        $this->integrationAuthorizationService = new AuthorizationService(
            $this->getMock('Magento\Framework\Acl\Builder', [], [], '', false),
            $roleFactoryMock,
            $this->getMock('Magento\Authorization\Model\Resource\Role\CollectionFactory', [], [], '', false),
            $this->getMock('Magento\Authorization\Model\RulesFactory', [], [], '', false),
            $this->getMock('Magento\Authorization\Model\Resource\Rules\CollectionFactory', [], [], '', false),
            $this->getMock('Psr\Log\LoggerInterface'),
            $this->getMock('Magento\Framework\Acl\RootResource', [], [], '', false)
        );
    }

    public function testRemovePermissions()
    {
        $integrationId = 22;
        $roleName = UserContextInterface::USER_TYPE_INTEGRATION . $integrationId;
        $this->roleMock->expects($this->once())->method('load')->with($roleName)->will($this->returnSelf());
        $this->integrationAuthorizationService->removePermissions($integrationId);
    }
}
