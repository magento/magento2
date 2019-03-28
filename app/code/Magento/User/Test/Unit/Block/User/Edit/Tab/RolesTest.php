<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\User\Test\Unit\Block\User\Edit\Tab;

/**
 * Class UserTest to cover Magento\User\Block\User\Edit\Tab\Roles
 *
 */
class RolesTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\User\Block\User\Edit\Tab\Roles */
    protected $model;

    /** @var \Magento\Backend\Helper\Data|\PHPUnit_Framework_MockObject_MockObject */
    protected $backendHelperMock;

    /** @var \Magento\Framework\Json\EncoderInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $jsonEncoderMock;

    /** @var \Magento\Framework\Json\DecoderInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $jsonDecoderMock;

    /** @var \Magento\Framework\Registry|\PHPUnit_Framework_MockObject_MockObject */
    protected $registryMock;

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
        $this->backendHelperMock = $this->getMockBuilder('Magento\Backend\Helper\Data')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->jsonEncoderMock = $this->getMockBuilder('Magento\Framework\Json\EncoderInterface')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->jsonDecoderMock = $this->getMockBuilder('Magento\Framework\Json\DecoderInterface')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->registryMock = $this->getMockBuilder('Magento\Framework\Registry')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->userRolesFactoryMock = $this
            ->getMockBuilder('Magento\Authorization\Model\ResourceModel\Role\CollectionFactory')
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

        $this->layoutMock = $this->getMockBuilder('Magento\Framework\View\LayoutInterface')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->filesystemMock = $this->getMockBuilder('Magento\Framework\Filesystem')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $objectManagerHelper->getObject(
            'Magento\User\Block\User\Edit\Tab\Roles',
            [
                'backendHelper' => $this->backendHelperMock,
                'jsonEncoder' => $this->jsonEncoderMock,
                'jsonDecoder' => $this->jsonDecoderMock,
                'userRolesFactory' => $this->userRolesFactoryMock,
                'coreRegistry' => $this->registryMock,
                'request' => $this->requestInterfaceMock,
                'urlBuilder' => $this->urlInterfaceMock,
                'layout' => $this->layoutMock,
                'filesystem' => $this->filesystemMock
            ]
        );
    }

    public function testGetUsersIncorrectInRoleUser()
    {
        $param = 'user_roles';
        $paramValue = 'not_JSON';
        $this->requestInterfaceMock->expects($this->once())->method('getParam')->with($param)->willReturn($paramValue);
        $this->jsonDecoderMock->expects($this->once())->method('decode')->with($paramValue)->willReturn(null);
        $this->assertEquals('{}', $this->model->getSelectedRoles(true));
    }
}
