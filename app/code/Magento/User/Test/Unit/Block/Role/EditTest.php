<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\User\Test\Unit\Block\Role;

use Magento\Backend\Block\Widget\Tabs;
use Magento\Backend\Model\Auth\Session;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Registry;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\LayoutInterface;
use Magento\User\Block\Role\Edit;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class EditTest to cover Magento\User\Block\Role\Edit
 *
 */
class EditTest extends TestCase
{
    /** @var Edit|MockObject */
    protected $model;

    /** @var EncoderInterface|MockObject */
    protected $jsonEncoderMock;

    /** @var Session|MockObject */
    protected $authSessionsMock;

    /** @var Registry|MockObject */
    protected $registryMock;

    /** @var LayoutInterface|MockObject */
    protected $layoutInterfaceMock;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->jsonEncoderMock = $this->getMockBuilder(EncoderInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMockForAbstractClass();

        $this->authSessionsMock = $this->getMockBuilder(Session::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->registryMock = $this->getMockBuilder(Registry::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->layoutInterfaceMock = $this->getMockBuilder(LayoutInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['setRole', 'setActive', 'getId'])
            ->getMockForAbstractClass();

        $objectManagerHelper = new ObjectManager($this);
        $this->model = $objectManagerHelper->getObject(
            Edit::class,
            [
                'jsonEncoder' => $this->jsonEncoderMock,
                'authSession' => $this->authSessionsMock,
                'registry' => $this->registryMock,
                'layout' => $this->layoutInterfaceMock
            ]
        );
    }

    public function testPrepareLayoutSuccessOnFalseGetId()
    {
        $tab = 'tab';

        $this->registryMock->expects($this->once())->method('registry')->willReturn($this->layoutInterfaceMock);
        $this->layoutInterfaceMock->expects($this->any())->method('createBlock')->willReturnSelf();
        $this->layoutInterfaceMock->expects($this->once())->method('setRole')->willReturnSelf();
        $this->layoutInterfaceMock->expects($this->once())->method('setActive')->willReturn($tab);
        $this->layoutInterfaceMock->expects($this->once())->method('getId')->willReturn(false);

        $this->assertInstanceOf(
            Tabs::class,
            $this->invokeMethod($this->model, '_prepareLayout')
        );
    }

    /**
     * Call protected/private method of a class.
     *
     * @param object &$object
     * @param string $methodName
     * @param array  $parameters
     *
     * @return mixed Method return.
     */
    private function invokeMethod(&$object, $methodName, array $parameters = [])
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }
}
