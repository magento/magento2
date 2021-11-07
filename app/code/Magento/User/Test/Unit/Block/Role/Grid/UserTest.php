<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\User\Test\Unit\Block\Role\Grid;

use Magento\Authorization\Model\Role;
use Magento\Authorization\Model\RoleFactory;
use Magento\Backend\Helper\Data;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\ReadInterface;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Registry;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\AbstractBlock;
use Magento\Framework\View\LayoutInterface;
use Magento\User\Block\Role\Grid\User;
use Magento\User\Controller\Adminhtml\User\Role\SaveRole;
use Magento\User\Model\ResourceModel\Role\User\CollectionFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class UserTest to cover Magento\User\Block\Role\Grid\User
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class UserTest extends TestCase
{
    /**
     * @var User
     */
    protected $model;

    /**
     * @var Data|MockObject
     */
    protected $backendHelperMock;

    /**
     * @var EncoderInterface|MockObject
     */
    protected $jsonEncoderMock;

    /**
     * @var Registry|MockObject
     */
    protected $registryMock;

    /**
     * @var RoleFactory|MockObject
     */
    protected $roleFactoryMock;

    /**
     * @var CollectionFactory|MockObject
     */
    protected $userRolesFactoryMock;

    /**
     * @var RequestInterface|MockObject
     */
    protected $requestInterfaceMock;

    /**
     * @var UrlInterface|MockObject
     */
    protected $urlInterfaceMock;

    /**
     * @var LayoutInterface|MockObject
     */
    protected $layoutMock;

    /**
     * @var Filesystem|MockObject
     */
    protected $filesystemMock;

    protected function setUp(): void
    {
        $this->backendHelperMock = $this->getMockBuilder(Data::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->jsonEncoderMock = $this->getMockBuilder(EncoderInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->registryMock = $this->getMockBuilder(Registry::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->roleFactoryMock = $this->getMockBuilder(RoleFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();

        $this->userRolesFactoryMock = $this
            ->getMockBuilder(CollectionFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();

        $this->requestInterfaceMock = $this->getMockBuilder(RequestInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->urlInterfaceMock = $this->getMockBuilder(UrlInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->layoutMock = $this->getMockBuilder(LayoutInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->filesystemMock = $this->getMockBuilder(Filesystem::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManagerHelper = new ObjectManager($this);
        $this->model = $objectManagerHelper->getObject(
            User::class,
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

    /**
     * @return void
     */
    public function testGetGridUrlSuccessfulUrl(): void
    {
        $roleId = 1;
        $url = 'http://Success';

        $this->requestInterfaceMock->expects($this->once())->method('getParam')->willReturn($roleId);
        $this->urlInterfaceMock->expects($this->once())->method('getUrl')->willReturn($url);

        $this->assertEquals($url, $this->model->getGridUrl());
    }

    /**
     * @return void
     */
    public function testGetUsersPositiveNumberOfRolesAndJsonFalse(): void
    {
        $roleId = 1;
        $roles = ['role1', 'role2', 'role3'];
        /** @var Role|MockObject */
        $roleModelMock = $this->getMockBuilder(Role::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->requestInterfaceMock->method('getParam')
            ->willReturnOnConsecutiveCalls('', $roleId);

        $this->registryMock->expects($this->once())
            ->method('registry')
            ->with(SaveRole::IN_ROLE_USER_FORM_DATA_SESSION_KEY)
            ->willReturn(null);

        $this->roleFactoryMock->expects($this->once())->method('create')->willReturn($roleModelMock);

        $roleModelMock->expects($this->once())->method('setId')->willReturnSelf();
        $roleModelMock->expects($this->once())->method('getRoleUsers')->willReturn($roles);

        $this->assertEquals($roles, $this->model->getUsers());
    }

    /**
     * @return void
     */
    public function testGetUsersPositiveNumberOfRolesAndJsonTrue(): void
    {
        $roleId = 1;
        $roles = ['role1', 'role2', 'role3'];
        /** @var Role|MockObject */
        $roleModelMock = $this->getMockBuilder(Role::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->requestInterfaceMock->method('getParam')
            ->willReturnOnConsecutiveCalls('', $roleId);

        $this->registryMock->expects($this->once())
            ->method('registry')
            ->with(SaveRole::IN_ROLE_USER_FORM_DATA_SESSION_KEY)
            ->willReturn('role1=value1&role2=value2&role3=value3');

        $this->roleFactoryMock->expects($this->never())->method('create')->willReturn($roleModelMock);
        $this->jsonEncoderMock->expects($this->once())->method('encode')->willReturn($roles);

        $this->assertEquals($roles, $this->model->getUsers(true));
    }

    /**
     * @return void
     */
    public function testGetUsersNoRolesAndJsonFalse(): void
    {
        $roleId = 1;
        $roles = [];
        /** @var Role|MockObject */
        $roleModelMock = $this->getMockBuilder(Role::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->requestInterfaceMock->method('getParam')
            ->willReturnOnConsecutiveCalls('', $roleId);

        $this->registryMock->expects($this->once())
            ->method('registry')
            ->with(SaveRole::IN_ROLE_USER_FORM_DATA_SESSION_KEY)
            ->willReturn(null);

        $this->roleFactoryMock->expects($this->once())->method('create')->willReturn($roleModelMock);
        $roleModelMock->expects($this->once())->method('setId')->willReturnSelf();
        $roleModelMock->expects($this->once())->method('getRoleUsers')->willReturn($roles);

        $this->assertEquals($roles, $this->model->getUsers());
    }

    /**
     * @return void
     */
    public function testPrepareColumns(): void
    {
        $this->requestInterfaceMock->expects($this->any())->method('getParam')->willReturn(1);
        $layoutBlockMock = $this->getMockBuilder(LayoutInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $blockMock = $this->getMockBuilder(AbstractBlock::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['setData', 'getLayout', 'getChildNames'])
            ->addMethods(['setGrid', 'setId', 'isAvailable'])
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
        $directoryMock = $this->getMockBuilder(ReadInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
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

    /**
     * @return void
     */
    public function testGetUsersCorrectInRoleUser(): void
    {
        $param = 'in_role_user';
        $paramValue = '{"a":"role1","1":"role2","2":"role3"}';
        $this->requestInterfaceMock->expects($this->once())->method('getParam')->with($param)->willReturn($paramValue);
        $this->jsonEncoderMock->expects($this->once())->method('encode')->willReturn($paramValue);
        $this->assertEquals($paramValue, $this->model->getUsers(true));
    }

    /**
     * @return void
     */
    public function testGetUsersIncorrectInRoleUser(): void
    {
        $param = 'in_role_user';
        $paramValue = 'not_JSON';
        $this->requestInterfaceMock->expects($this->once())->method('getParam')->with($param)->willReturn($paramValue);
        $this->assertEquals('{}', $this->model->getUsers(true));
    }
}
