<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\PageCache\Test\Unit\Model\App\FrontController;

use Magento\PageCache\Model\App\FrontController\VarnishPlugin;

class VarnishPluginTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var VarnishPlugin
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
        $this->plugin = new \Magento\PageCache\Model\App\FrontController\VarnishPlugin(
            $this->configMock,
            $this->versionMock,
            $this->stateMock
        );
    }

    /**
     * @dataProvider dataProvider
     */
    public function testAroundDispatchReturnsCache($state, $countHeader, $countProcess, $countGetMode, $response)
    {
        $this->configMock
            ->expects($this->once())
            ->method('isEnabled')
            ->will($this->returnValue(true));
        $this->configMock
            ->expects($this->once())
            ->method('getType')
            ->will($this->returnValue(\Magento\PageCache\Model\Config::VARNISH));
        $this->versionMock
            ->expects($countProcess)
            ->method('process');
        $this->stateMock->expects($countGetMode)
            ->method('getMode')
            ->will($this->returnValue($state));
        $response->expects($countHeader)
            ->method('setHeader')
            ->with('X-Magento-Debug');

        $this->closure = function () use ($response) {
            return $response;
        };

        $this->plugin->aroundDispatch($this->frontControllerMock, $this->closure, $this->requestMock);
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
        $this->versionMock
            ->expects($this->never())
            ->method('process');
        $this->stateMock->expects($this->any())
            ->method('getMode')
            ->will($this->returnValue($state));
        $this->responseMock->expects($this->never())
            ->method('setHeader');
        $this->plugin->aroundDispatch($this->frontControllerMock, $this->closure, $this->requestMock);
    }

    public function dataProvider()
    {
        return [
            'developer_mode' => [
                \Magento\Framework\App\State::MODE_DEVELOPER,
                $this->once(),
                $this->once(),
                $this->once(),
                $this->getMock('Magento\Framework\App\Response\Http', [], [], '', false),
            ],
            'production' => [
                \Magento\Framework\App\State::MODE_PRODUCTION,
                $this->never(),
                $this->never(),
                $this->never(),
                $this->getMock('Magento\Framework\Controller\ResultInterface', [], [], '', false),
            ],
        ];
    }
}
