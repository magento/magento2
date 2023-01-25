<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\PageCache\Test\Unit\Model\App\FrontController;

use Closure;
use Laminas\Http\Header\GenericHeader;
use Magento\Framework\App\FrontControllerInterface;
use Magento\Framework\App\PageCache\Kernel;
use Magento\Framework\App\PageCache\Version;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Response\Http;
use Magento\Framework\App\State;
use Magento\Framework\Controller\ResultInterface;
use Magento\PageCache\Model\App\FrontController\BuiltinPlugin;
use Magento\PageCache\Model\Config;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class BuiltinPluginTest extends TestCase
{
    /**
     * @var BuiltinPlugin
     */
    protected $plugin;

    /**
     * @var Config|MockObject
     */
    protected $configMock;

    /**
     * @var Version|MockObject
     */
    protected $versionMock;

    /**
     * @var Kernel|MockObject
     */
    protected $kernelMock;

    /**
     * @var State|MockObject
     */
    protected $stateMock;

    /**
     * @var Http|MockObject
     */
    protected $responseMock;

    /**
     * @var FrontControllerInterface|MockObject
     */
    protected $frontControllerMock;

    /**
     * @var Closure
     */
    protected $closure;

    /**
     * @var RequestInterface|MockObject
     */
    protected $requestMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->configMock = $this->createMock(Config::class);
        $this->versionMock = $this->createMock(Version::class);
        $this->kernelMock = $this->createMock(Kernel::class);
        $this->stateMock = $this->createMock(State::class);
        $this->frontControllerMock = $this->getMockForAbstractClass(FrontControllerInterface::class);
        $this->requestMock = $this->getMockForAbstractClass(RequestInterface::class);
        $this->responseMock = $this->createMock(Http::class);
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
     * @return void
     * @dataProvider dataProvider
     */
    public function testAroundDispatchProcessIfCacheMissed($state): void
    {
        $header = GenericHeader::fromString('Cache-Control: no-cache');
        $this->configMock
            ->expects($this->once())
            ->method('getType')
            ->willReturn(Config::BUILT_IN);
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
        if ($state == State::MODE_DEVELOPER) {
            $this->responseMock
                ->method('setHeader')
                ->withConsecutive(
                    ['X-Magento-Cache-Control'],
                    ['X-Magento-Cache-Debug', 'MISS', true]
                );
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
     * @return void
     * @dataProvider dataProvider
     */
    public function testAroundDispatchReturnsResultInterfaceProcessIfCacheMissed($state): void
    {
        $this->configMock
            ->expects($this->once())
            ->method('getType')
            ->willReturn(Config::BUILT_IN);
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

        $result = $this->getMockForAbstractClass(ResultInterface::class);
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
     * @return void
     * @dataProvider dataProvider
     */
    public function testAroundDispatchReturnsCache($state): void
    {
        $this->configMock
            ->expects($this->once())
            ->method('getType')
            ->willReturn(Config::BUILT_IN);
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
        if ($state == State::MODE_DEVELOPER) {
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
     * @return void
     * @dataProvider dataProvider
     */
    public function testAroundDispatchDisabled($state): void
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
    public function dataProvider(): array
    {
        return [
            'developer_mode' => [State::MODE_DEVELOPER],
            'production' => [State::MODE_PRODUCTION]
        ];
    }
}
