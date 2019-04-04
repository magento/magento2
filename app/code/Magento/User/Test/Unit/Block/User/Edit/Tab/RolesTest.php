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

    /** @var \Magento\Framework\Json\EncoderInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $jsonEncoderMock;

    /** @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $requestInterfaceMock;

    protected function setUp()
    {
        $this->jsonEncoderMock = $this->getMockBuilder('Magento\Framework\Json\EncoderInterface')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->requestInterfaceMock = $this->getMockBuilder('Magento\Framework\App\RequestInterface')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $objectManagerHelper->getObject(
            'Magento\User\Block\User\Edit\Tab\Roles',
            [
                'jsonEncoder' => $this->jsonEncoderMock,
                'request' => $this->requestInterfaceMock,
            ]
        );
    }

    public function testSelectedRolesCorrectUserRoles()
    {
        $param = 'user_roles';
        $paramValue = '{"a":"role1","1":"role2","2":"role3"}';
        $this->requestInterfaceMock->expects($this->once())->method('getParam')->with($param)->willReturn($paramValue);
        $this->jsonEncoderMock->expects($this->once())->method('encode')->willReturn($paramValue);
        $this->assertEquals($paramValue, $this->model->getSelectedRoles(true));
    }

    public function testSelectedRolesIncorrectInRoleUser()
    {
        $param = 'user_roles';
        $paramValue = 'not_JSON';
        $this->requestInterfaceMock->expects($this->once())->method('getParam')->with($param)->willReturn($paramValue);
        $this->assertEquals('{}', $this->model->getSelectedRoles(true));
    }
}
