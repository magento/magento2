<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\User\Test\Unit\Block\Role;

/**
 * Class EditTest to cover Magento\User\Block\Role\Edit
 *
 */
class EditTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\User\Block\Role\Edit|\PHPUnit_Framework_MockObject_MockObject */
    protected $model;

    /** @var \Magento\Framework\Json\EncoderInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $jsonEncoderMock;

    /** @var \Magento\Backend\Model\Auth\Session|\PHPUnit_Framework_MockObject_MockObject */
    protected $authSessionsMock;

    /** @var \Magento\Framework\Registry|\PHPUnit_Framework_MockObject_MockObject */
    protected $registryMock;

    /** @var \Magento\Framework\View\LayoutInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $layoutInterfaceMock;

    /**
     * Set required values
     * @return void
     */
    protected function setUp()
    {
        $this->jsonEncoderMock = $this->getMockBuilder(\Magento\Framework\Json\EncoderInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->authSessionsMock = $this->getMockBuilder(\Magento\Backend\Model\Auth\Session::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->registryMock = $this->getMockBuilder(\Magento\Framework\Registry::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->layoutInterfaceMock = $this->getMockBuilder(\Magento\Framework\View\LayoutInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['setRole', 'setActive', 'getId'])
            ->getMockForAbstractClass();

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $objectManagerHelper->getObject(
            \Magento\User\Block\Role\Edit::class,
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
            \Magento\Backend\Block\Widget\Tabs::class,
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
