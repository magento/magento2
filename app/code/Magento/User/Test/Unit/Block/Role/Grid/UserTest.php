<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\User\Test\Unit\Block\Role\Grid;

/**
 * Class UserTest to cover Magento\User\Block\Role\Grid\User
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class UserTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\User\Block\Role\Grid\User */
    protected $model;

    /** @var \Magento\Backend\Helper\Data|\PHPUnit_Framework_MockObject_MockObject */
    protected $backendHelperMock;

    /** @var \Magento\Framework\Json\EncoderInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $jsonEncoderMock;

    /** @var \Magento\Framework\Registry|\PHPUnit_Framework_MockObject_MockObject */
    protected $registryMock;

    /** @var \Magento\Authorization\Model\RoleFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $roleFactoryMock;

    /** @var \Magento\User\Model\ResourceModel\Role\User\CollectionFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $userRolesFactoryMock;

    /** @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $requestInterfaceMock;

    /** @var \Magento\Framework\UrlInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $urlInterfaceMock;

    /** @var \Magento\Framework\View\LayoutInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $layoutMock;

    /** @var \Magento\Framework\Filesystem|\PHPUnit_Framework_MockObject_MockObject */
    protected $filesystemMock;

    protected function setUp()
    {
        $this->backendHelperMock = $this->getMockBuilder(\Magento\Backend\Helper\Data::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->jsonEncoderMock = $this->getMockBuilder(\Magento\Framework\Json\EncoderInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->registryMock = $this->getMockBuilder(\Magento\Framework\Registry::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->roleFactoryMock = $this->getMockBuilder(\Magento\Authorization\Model\RoleFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->userRolesFactoryMock = $this
            ->getMockBuilder(\Magento\User\Model\ResourceModel\Role\User\CollectionFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->requestInterfaceMock = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->urlInterfaceMock = $this->getMockBuilder(\Magento\Framework\UrlInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->layoutMock = $this->getMockBuilder(\Magento\Framework\View\LayoutInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->filesystemMock = $this->getMockBuilder(\Magento\Framework\Filesystem::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $objectManagerHelper->getObject(
            \Magento\User\Block\Role\Grid\User::class,
            [
                'backendHelper' => $this->backendHelperMock,
                'jsonEncoder' => $this->jsonEncoderMock,
                'coreRegistry' => $this->registryMock,
                'roleFactory' => $this->roleFactoryMock,
                'userRolesFactory' => $this->userRolesFactoryMock,
                'request' => $this->requestInterfaceMock,
                'urlBuilder' => $this->urlInterfaceMock,
                'layout' => $this->layoutMock,
                'filesystem' => $this->filesystemMock
            ]
        );
    }

    public function testGetGridUrlSuccessfulUrl()
    {
        $roleId = 1;
        $url = 'http://Success';

        $this->requestInterfaceMock->expects($this->once())->method('getParam')->willReturn($roleId);
        $this->urlInterfaceMock->expects($this->once())->method('getUrl')->willReturn($url);

        $this->assertEquals($url, $this->model->getGridUrl());
    }

    public function testGetUsersPositiveNumberOfRolesAndJsonFalse()
    {
        $roleId = 1;
        $roles = ['role1', 'role2', 'role3'];
        /** @var \Magento\Authorization\Model\Role|\PHPUnit_Framework_MockObject_MockObject */
        $roleModelMock = $this->getMockBuilder(\Magento\Authorization\Model\Role::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->requestInterfaceMock->expects($this->at(0))->method('getParam')->willReturn("");
        $this->requestInterfaceMock->expects($this->at(1))->method('getParam')->willReturn($roleId);
        $this->requestInterfaceMock->expects($this->at(2))->method('getParam')->willReturn($roleId);

        $this->registryMock->expects($this->once())
            ->method('registry')
            ->with(\Magento\User\Controller\Adminhtml\User\Role\SaveRole::IN_ROLE_USER_FORM_DATA_SESSION_KEY)
            ->willReturn(null);

        $this->roleFactoryMock->expects($this->once())->method('create')->willReturn($roleModelMock);

        $roleModelMock->expects($this->once())->method('setId')->willReturnSelf();
        $roleModelMock->expects($this->once())->method('getRoleUsers')->willReturn($roles);

        $this->assertEquals($roles, $this->model->getUsers());
    }

    public function testGetUsersPositiveNumberOfRolesAndJsonTrue()
    {
        $roleId = 1;
        $roles = ['role1', 'role2', 'role3'];
        /** @var \Magento\Authorization\Model\Role|\PHPUnit_Framework_MockObject_MockObject */
        $roleModelMock = $this->getMockBuilder(\Magento\Authorization\Model\Role::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->requestInterfaceMock->expects($this->at(0))->method('getParam')->willReturn("");
        $this->requestInterfaceMock->expects($this->at(1))->method('getParam')->willReturn($roleId);
        $this->requestInterfaceMock->expects($this->at(2))->method('getParam')->willReturn($roleId);

        $this->registryMock->expects($this->once())
            ->method('registry')
            ->with(\Magento\User\Controller\Adminhtml\User\Role\SaveRole::IN_ROLE_USER_FORM_DATA_SESSION_KEY)
            ->willReturn('role1=value1&role2=value2&role3=value3');

        $this->roleFactoryMock->expects($this->never())->method('create')->willReturn($roleModelMock);
        $this->jsonEncoderMock->expects($this->once())->method('encode')->willReturn($roles);

        $this->assertEquals($roles, $this->model->getUsers(true));
    }

    public function testGetUsersNoRolesAndJsonFalse()
    {
        $roleId = 1;
        $roles = [];
        /** @var \Magento\Authorization\Model\Role|\PHPUnit_Framework_MockObject_MockObject */
        $roleModelMock = $this->getMockBuilder(\Magento\Authorization\Model\Role::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->requestInterfaceMock->expects($this->at(0))->method('getParam')->willReturn("");
        $this->requestInterfaceMock->expects($this->at(1))->method('getParam')->willReturn($roleId);
        $this->requestInterfaceMock->expects($this->at(2))->method('getParam')->willReturn($roleId);

        $this->registryMock->expects($this->once())
            ->method('registry')
            ->with(\Magento\User\Controller\Adminhtml\User\Role\SaveRole::IN_ROLE_USER_FORM_DATA_SESSION_KEY)
            ->willReturn(null);

        $this->roleFactoryMock->expects($this->once())->method('create')->willReturn($roleModelMock);
        $roleModelMock->expects($this->once())->method('setId')->willReturnSelf();
        $roleModelMock->expects($this->once())->method('getRoleUsers')->willReturn($roles);

        $this->assertEquals($roles, $this->model->getUsers());
    }

    public function testPrepareColumns()
    {
        $this->requestInterfaceMock->expects($this->any())->method('getParam')->willReturn(1);
        $layoutBlockMock = $this->getMockBuilder(\Magento\Framework\View\LayoutInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $blockMock = $this->getMockBuilder(\Magento\Framework\View\Element\AbstractBlock::class)
            ->disableOriginalConstructor()
            ->setMethods(['setGrid', 'setId', 'setData', 'getLayout', 'getChildNames', 'isAvailable'])
            ->setMockClassName('mainblock')
            ->getMock();
        $blockMock->expects($this->any())->method('getLayout')->willReturn($layoutBlockMock);
        $this->layoutMock->expects($this->any())->method('getChildName')->willReturn('name');
        $this->layoutMock->expects($this->any())->method('getBlock')->willReturn($blockMock);
        $this->layoutMock->expects($this->any())->method('createBlock')->willReturn($blockMock);
        $blockMock->expects($this->any())->method('isAvailable')->willReturn(false);
        $blockMock->expects($this->any())->method('setData')->willReturnSelf();
        $blockMock->expects($this->any())->method('setGrid')->willReturnSelf();
        $blockMock->expects($this->any())->method('getChildNames')->willReturn(['column']);
        $layoutBlockMock->expects($this->any())->method('getChildName')->willReturn('name');
        $layoutBlockMock->expects($this->any())->method('getBlock')->willReturn($blockMock);
        $layoutBlockMock->expects($this->any())->method('createBlock')->willReturn($blockMock);
        $directoryMock = $this->getMockBuilder(\Magento\Framework\Filesystem\Directory\ReadInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->filesystemMock->expects($this->any())->method('getDirectoryRead')->willReturn($directoryMock);
        $directoryMock->expects($this->any())->method('getRelativePath')->willReturn('filename');

        $blockMock->expects($this->exactly(7))->method('setId')->withConsecutive(
            ['in_role_users'],
            ['role_user_id'],
            ['role_user_username'],
            ['role_user_firstname'],
            ['role_user_lastname'],
            ['role_user_email'],
            ['role_user_is_active']
        )->willReturnSelf();

        $this->model->toHtml();
    }
}
