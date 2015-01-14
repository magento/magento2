<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\PageCache\Model\App\FrontController;

class BuiltinPluginTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var BuiltinPlugin
     */
    protected $plugin;

    /**
     * @var \Magento\PageCache\Model\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $configMock;

    /**
     * @var \Magento\Framework\App\PageCache\Version|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $versionMock;

    /**
     * @var \Magento\Framework\App\PageCache\Kernel|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $kernelMock;

    /**
     * @var \Magento\Framework\App\State|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $stateMock;

    /**
     * @var \Magento\Framework\App\Response\Http|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $responseMock;

    /**
     * @var \Magento\Framework\App\FrontControllerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $frontControllerMock;

    /**
     * @var \Closure
     */
    protected $closure;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestMock;

    /**
     * SetUp
     */
    public function setUp()
    {
        $this->configMock = $this->getMock('Magento\PageCache\Model\Config', [], [], '', false);
        $this->versionMock = $this->getMock('Magento\Framework\App\PageCache\Version', [], [], '', false);
        $this->kernelMock = $this->getMock('Magento\Framework\App\PageCache\Kernel', [], [], '', false);
        $this->stateMock = $this->getMock('Magento\Framework\App\State', [], [], '', false);
        $this->frontControllerMock = $this->getMock(
            'Magento\Framework\App\FrontControllerInterface',
            [],
            [],
            '',
            false
        );
        $this->requestMock = $this->getMock('Magento\Framework\App\RequestInterface', [], [], '', false);
        $this->responseMock = $this->getMock('Magento\Framework\App\Response\Http', [], [], '', false);
        $response = $this->responseMock;
        $this->closure = function () use ($response) {
            return $response;
        };
        $this->plugin = new BuiltinPlugin(
            $this->configMock,
            $this->versionMock,
            $this->kernelMock,
            $this->stateMock
        );
    }

    /**
     * @dataProvider dataProvider
     */
    public function testAroundDispatchProcessIfCacheMissed($state)
    {
        $this->configMock
            ->expects($this->once())
            ->method('getType')
            ->will($this->returnValue(\Magento\PageCache\Model\Config::BUILT_IN));
        $this->configMock->expects($this->once())
            ->method('isEnabled')
            ->will($this->returnValue(true));
        $this->versionMock
            ->expects($this->once())
            ->method('process');
        $this->kernelMock
            ->expects($this->once())
            ->method('load')
            ->will($this->returnValue(false));
        $this->stateMock->expects($this->any())
            ->method('getMode')
            ->will($this->returnValue($state));
        if ($state == \Magento\Framework\App\State::MODE_DEVELOPER) {
            $this->responseMock->expects($this->at(1))
                ->method('setHeader')
                ->with('X-Magento-Cache-Control');
            $this->responseMock->expects($this->at(2))
                ->method('setHeader')
                ->with('X-Magento-Cache-Debug', 'MISS', true);
        } else {
            $this->responseMock->expects($this->never())
                ->method('setHeader');
        }
        $this->kernelMock
            ->expects($this->once())
            ->method('process')
            ->with($this->responseMock);
        $this->assertSame(
            $this->responseMock,
            $this->plugin->aroundDispatch($this->frontControllerMock, $this->closure, $this->requestMock)
        );
    }

    /**
     * @dataProvider dataProvider
     */
    public function testAroundDispatchReturnsResultInterfaceProcessIfCacheMissed($state)
    {
        $this->configMock
            ->expects($this->once())
            ->method('getType')
            ->will($this->returnValue(\Magento\PageCache\Model\Config::BUILT_IN));
        $this->configMock->expects($this->once())
            ->method('isEnabled')
            ->will($this->returnValue(true));
        $this->versionMock
            ->expects($this->once())
            ->method('process');
        $this->kernelMock
            ->expects($this->once())
            ->method('load')
            ->will($this->returnValue(false));
        $this->stateMock->expects($this->any())
            ->method('getMode')
            ->will($this->returnValue($state));

        $result = $this->getMock('Magento\Framework\Controller\ResultInterface', [], [], '', false);
        $result->expects($this->never())->method('setHeader');
        $closure =  function () use ($result) {
            return $result;
        };

        $this->assertSame(
            $result,
            $this->plugin->aroundDispatch($this->frontControllerMock, $closure, $this->requestMock)
        );
    }

    /**
     * @dataProvider dataProvider
     */
    public function testAroundDispatchReturnsCache($state)
    {
        $this->configMock
            ->expects($this->once())
            ->method('getType')
            ->will($this->returnValue(\Magento\PageCache\Model\Config::BUILT_IN));
        $this->configMock->expects($this->once())
            ->method('isEnabled')
            ->will($this->returnValue(true));
        $this->versionMock
            ->expects($this->once())
            ->method('process');
        $this->kernelMock
            ->expects($this->once())
            ->method('load')
            ->will($this->returnValue($this->responseMock));

        $this->stateMock->expects($this->any())
            ->method('getMode')
            ->will($this->returnValue($state));
        if ($state == \Magento\Framework\App\State::MODE_DEVELOPER) {
            $this->responseMock->expects($this->once())
                ->method('setHeader')
                ->with('X-Magento-Cache-Debug');
        } else {
            $this->responseMock->expects($this->never())
                ->method('setHeader');
        }
        $this->assertSame(
            $this->responseMock,
            $this->plugin->aroundDispatch($this->frontControllerMock, $this->closure, $this->requestMock)
        );
    }

    /**
     * @dataProvider dataProvider
     */
    public function testAroundDispatchDisabled($state)
    {
        $this->configMock
            ->expects($this->any())
            ->method('getType')
            ->will($this->returnValue(null));
        $this->configMock->expects($this->any())
            ->method('isEnabled')
            ->will($this->returnValue(true));
        $this->versionMock
            ->expects($this->never())
            ->method('process');
        $this->stateMock->expects($this->any())
            ->method('getMode')
            ->will($this->returnValue($state));
        $this->responseMock->expects($this->never())
            ->method('setHeader');
        $this->assertSame(
            $this->responseMock,
            $this->plugin->aroundDispatch($this->frontControllerMock, $this->closure, $this->requestMock)
        );
    }

    public function dataProvider()
    {
        return [
            'developer_mode' => [\Magento\Framework\App\State::MODE_DEVELOPER],
            'production' => [\Magento\Framework\App\State::MODE_PRODUCTION],
        ];
    }
}
