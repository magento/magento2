<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\User\Test\Unit\Block\Role\Grid;

/**
 * Class UserTest to cover Magento\User\Block\Role\Grid\User
 *
 */
class UserTest extends \PHPUnit_Framework_TestCase
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

    protected function setUp()
    {
        $this->backendHelperMock = $this->getMockBuilder('Magento\Backend\Helper\Data')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->jsonEncoderMock = $this->getMockBuilder('Magento\Framework\Json\EncoderInterface')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->registryMock = $this->getMockBuilder('Magento\Framework\Registry')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->roleFactoryMock = $this->getMockBuilder('Magento\Authorization\Model\RoleFactory')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->userRolesFactoryMock = $this
            ->getMockBuilder('Magento\User\Model\ResourceModel\Role\User\CollectionFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->requestInterfaceMock = $this->getMockBuilder('Magento\Framework\App\RequestInterface')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->urlInterfaceMock = $this->getMockBuilder('Magento\Framework\UrlInterface')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $objectManagerHelper->getObject(
            'Magento\User\Block\Role\Grid\User',
            [
                'backendHelper' => $this->backendHelperMock,
                'jsonEncoder' => $this->jsonEncoderMock,
                'coreRegistry' => $this->registryMock,
                'roleFactory' => $this->roleFactoryMock,
                'userRolesFactory' => $this->userRolesFactoryMock,
                'request' => $this->requestInterfaceMock,
                'urlBuilder' => $this->urlInterfaceMock
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
        $roleModelMock = $this->getMockBuilder('Magento\Authorization\Model\Role')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->requestInterfaceMock->expects($this->at(0))->method('getParam')->willReturn("");
        $this->requestInterfaceMock->expects($this->at(1))->method('getParam')->willReturn($roleId);
        $this->requestInterfaceMock->expects($this->at(2))->method('getParam')->willReturn($roleId);
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
        $roleModelMock = $this->getMockBuilder('Magento\Authorization\Model\Role')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->requestInterfaceMock->expects($this->at(0))->method('getParam')->willReturn("");
        $this->requestInterfaceMock->expects($this->at(1))->method('getParam')->willReturn($roleId);
        $this->requestInterfaceMock->expects($this->at(2))->method('getParam')->willReturn($roleId);
        $this->roleFactoryMock->expects($this->once())->method('create')->willReturn($roleModelMock);
        $roleModelMock->expects($this->once())->method('setId')->willReturnSelf();
        $roleModelMock->expects($this->once())->method('getRoleUsers')->willReturn($roles);
        $this->jsonEncoderMock->expects($this->once())->method('encode')->willReturn($roles);

        $this->assertEquals($roles, $this->model->getUsers(true));
    }

    public function testGetUsersNoRolesAndJsonFalse()
    {
        $roleId = 1;
        $roles = [];
        /** @var \Magento\Authorization\Model\Role|\PHPUnit_Framework_MockObject_MockObject */
        $roleModelMock = $this->getMockBuilder('Magento\Authorization\Model\Role')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->requestInterfaceMock->expects($this->at(0))->method('getParam')->willReturn("");
        $this->requestInterfaceMock->expects($this->at(1))->method('getParam')->willReturn($roleId);
        $this->requestInterfaceMock->expects($this->at(2))->method('getParam')->willReturn($roleId);
        $this->roleFactoryMock->expects($this->once())->method('create')->willReturn($roleModelMock);
        $roleModelMock->expects($this->once())->method('setId')->willReturnSelf();
        $roleModelMock->expects($this->once())->method('getRoleUsers')->willReturn($roles);

        $this->assertEquals($roles, $this->model->getUsers());
    }
}
