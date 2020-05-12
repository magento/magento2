<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\User\Test\Unit\Block\User\Edit\Tab;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\User\Block\User\Edit\Tab\Roles;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class RolesTest to cover \Magento\User\Block\User\Edit\Tab\Roles
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RolesTest extends TestCase
{
    /** @var Roles */
    protected $model;

    /** @var EncoderInterface|MockObject */
    protected $jsonEncoderMock;

    /** @var RequestInterface|MockObject */
    protected $requestInterfaceMock;

    protected function setUp(): void
    {
        $this->jsonEncoderMock = $this->getMockBuilder(EncoderInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMockForAbstractClass();

        $this->requestInterfaceMock = $this->getMockBuilder(RequestInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMockForAbstractClass();

        $objectManagerHelper = new ObjectManager($this);
        $this->model = $objectManagerHelper->getObject(
            Roles::class,
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

    public function testSelectedRolesIncorrectUserRoles()
    {
        $param = 'user_roles';
        $paramValue = 'not_JSON';
        $this->requestInterfaceMock->expects($this->once())->method('getParam')->with($param)->willReturn($paramValue);
        $this->assertEquals('{}', $this->model->getSelectedRoles(true));
    }
}
