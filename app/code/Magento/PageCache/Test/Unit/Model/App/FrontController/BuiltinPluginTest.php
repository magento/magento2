<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\PageCache\Test\Unit\Model\App\FrontController;

use Magento\PageCache\Model\App\FrontController\BuiltinPlugin;

class BuiltinPluginTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var BuiltinPlugin
     */
    protected $plugin;

    /**
     * @var \Magento\PageCache\Model\Config|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $configMock;

    /**
     * @var \Magento\Framework\App\PageCache\Version|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $versionMock;

    /**
     * @var \Magento\Framework\App\PageCache\Kernel|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $kernelMock;

    /**
     * @var \Magento\Framework\App\State|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $stateMock;

    /**
     * @var \Magento\Framework\App\Response\Http|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $responseMock;

    /**
     * @var \Magento\Framework\App\FrontControllerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $frontControllerMock;

    /**
     * @var \Closure
     */
    protected $closure;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $requestMock;

    /**
     * SetUp
     */
    protected function setUp(): void
    {
        $this->configMock = $this->createMock(\Magento\PageCache\Model\Config::class);
        $this->versionMock = $this->createMock(\Magento\Framework\App\PageCache\Version::class);
        $this->kernelMock = $this->createMock(\Magento\Framework\App\PageCache\Kernel::class);
        $this->stateMock = $this->createMock(\Magento\Framework\App\State::class);
        $this->frontControllerMock = $this->createMock(\Magento\Framework\App\FrontControllerInterface::class);
        $this->requestMock = $this->createMock(\Magento\Framework\App\RequestInterface::class);
        $this->responseMock = $this->createMock(\Magento\Framework\App\Response\Http::class);
        $response = $this->responseMock;
        $this->closure = function () use ($response) {
            return $response;
        };
        $this->plugin = new \Magento\PageCache\Model\App\FrontController\BuiltinPlugin(
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
        $header = \Zend\Http\Header\GenericHeader::fromString('Cache-Control: no-cache');
        $this->configMock
            ->expects($this->once())
            ->method('getType')
            ->willReturn(\Magento\PageCache\Model\Config::BUILT_IN);
        $this->configMock->expects($this->once())
            ->method('isEnabled')
            ->willReturn(true);
        $this->versionMock
            ->expects($this->once())
            ->method('process');
        $this->kernelMock
            ->expects($this->once())
            ->method('load')
            ->willReturn(false);
        $this->stateMock->expects($this->any())
            ->method('getMode')
            ->willReturn($state);
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
        $this->responseMock
            ->expects($this->once())
            ->method('getHeader')
            ->with('Cache-Control')
            ->willReturn($header);
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
            ->willReturn(\Magento\PageCache\Model\Config::BUILT_IN);
        $this->configMock->expects($this->once())
            ->method('isEnabled')
            ->willReturn(true);
        $this->versionMock
            ->expects($this->once())
            ->method('process');
        $this->kernelMock
            ->expects($this->once())
            ->method('load')
            ->willReturn(false);
        $this->stateMock->expects($this->any())
            ->method('getMode')
            ->willReturn($state);

        $result = $this->createMock(\Magento\Framework\Controller\ResultInterface::class);
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
            ->willReturn(\Magento\PageCache\Model\Config::BUILT_IN);
        $this->configMock->expects($this->once())
            ->method('isEnabled')
            ->willReturn(true);
        $this->versionMock
            ->expects($this->once())
            ->method('process');
        $this->kernelMock
            ->expects($this->once())
            ->method('load')
            ->willReturn($this->responseMock);

        $this->stateMock->expects($this->any())
            ->method('getMode')
            ->willReturn($state);
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
            ->willReturn(null);
        $this->configMock->expects($this->any())
            ->method('isEnabled')
            ->willReturn(true);
        $this->versionMock
            ->expects($this->once())
            ->method('process');
        $this->stateMock->expects($this->any())
            ->method('getMode')
            ->willReturn($state);
        $this->responseMock->expects($this->never())
            ->method('setHeader');
        $this->assertSame(
            $this->responseMock,
            $this->plugin->aroundDispatch($this->frontControllerMock, $this->closure, $this->requestMock)
        );
    }

    /**
     * @return array
     */
    public function dataProvider()
    {
        return [
            'developer_mode' => [\Magento\Framework\App\State::MODE_DEVELOPER],
            'production' => [\Magento\Framework\App\State::MODE_PRODUCTION],
        ];
    }
}
