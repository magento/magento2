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
            array('load', 'delete', '__wakeup'),
            array(),
            '',
            false
        );
        $this->roleMock->expects($this->any())->method('load')->will($this->returnSelf());
        $this->roleMock->expects($this->any())->method('delete')->will($this->returnSelf());

        /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Authorization\Model\RoleFactory $roleFactoryMock */
        $roleFactoryMock = $this->getMock(
            'Magento\Authorization\Model\RoleFactory',
            array('create'),
            array(),
            '',
            false
        );
        $roleFactoryMock->expects($this->any())->method('create')->will($this->returnValue($this->roleMock));

        $this->integrationAuthorizationService = new AuthorizationService(
            $this->getMock('Magento\Framework\Acl\Builder', array(), array(), '', false),
            $roleFactoryMock,
            $this->getMock('Magento\Authorization\Model\Resource\Role\CollectionFactory', array(), array(), '', false),
            $this->getMock('Magento\Authorization\Model\RulesFactory', array(), array(), '', false),
            $this->getMock('Magento\Authorization\Model\Resource\Rules\CollectionFactory', array(), array(), '', false),
            $this->getMock('Magento\Framework\Logger', array(), array(), '', false),
            $this->getMock('Magento\Framework\Acl\RootResource', array(), array(), '', false)
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
