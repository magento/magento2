<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\PageCache\Test\Unit\Model\Controller\Result;

use Laminas\Http\Header\HeaderInterface as HttpHeaderInterface;
use Magento\Framework\App\PageCache\Kernel;
use Magento\Framework\App\Response\Http as ResponseHttp;
use Magento\Framework\App\State as AppState;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Registry;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\PageCache\Model\Cache\Type as CacheType;
use Magento\PageCache\Model\Config;
use Magento\PageCache\Model\Controller\Result\BuiltinPlugin;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class BuiltinPluginTest extends TestCase
{
    /**
     * @var BuiltinPlugin
     */
    private $plugin;

    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var Config|MockObject
     */
    private $configMock;

    /**
     * @var Kernel|MockObject
     */
    private $kernelMock;

    /**
     * @var AppState|MockObject
     */
    private $stateMock;

    /**
     * @var Registry|MockObject
     */
    private $registryMock;

    /**
     * @var ResultInterface|MockObject
     */
    private $resultMock;

    /**
     * @var ResponseHttp|MockObject
     */
    private $responseMock;

    /**
     * @var HttpHeaderInterface|MockObject
     */
    private $httpHeaderMock;

    protected function setUp(): void
    {
        $this->configMock = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->kernelMock = $this->getMockBuilder(Kernel::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->stateMock = $this->getMockBuilder(AppState::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->registryMock = $this->getMockBuilder(Registry::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultMock = $this->getMockBuilder(ResultInterface::class)
            ->getMockForAbstractClass();
        $this->responseMock = $this->getMockBuilder(ResponseHttp::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->httpHeaderMock = $this->getMockBuilder(HttpHeaderInterface::class)
            ->getMockForAbstractClass();

        $this->responseMock->expects(static::any())
            ->method('getHeader')
            ->willReturnMap(
                [
                    ['X-Magento-Tags', $this->httpHeaderMock],
                    ['Cache-Control', $this->httpHeaderMock]
                ]
            );
        $this->configMock->expects(static::any())
            ->method('isEnabled')
            ->willReturn(true);
        $this->configMock->expects(static::any())
            ->method('getType')
            ->willReturn(Config::BUILT_IN);

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->plugin = $this->objectManagerHelper->getObject(
            BuiltinPlugin::class,
            [
                'registry' => $this->registryMock,
                'config' => $this->configMock,
                'kernel' => $this->kernelMock,
                'state' => $this->stateMock
            ]
        );
    }

    public function testAfterResultWithoutPlugin()
    {
        $this->registryMock->expects(static::once())
            ->method('registry')
            ->with('use_page_cache_plugin')
            ->willReturn(false);
        $this->kernelMock->expects(static::never())
            ->method('process')
            ->with($this->responseMock);

        $this->assertSame(
            $this->resultMock,
            $this->plugin->afterRenderResult($this->resultMock, $this->resultMock, $this->responseMock)
        );
    }

    public function testAfterResultWithPlugin()
    {
        $this->registryMock->expects(static::once())
            ->method('registry')
            ->with('use_page_cache_plugin')
            ->willReturn(true);
        $this->stateMock->expects(static::once())
            ->method('getMode')
            ->willReturn(null);
        $this->httpHeaderMock->expects(static::any())
            ->method('getFieldValue')
            ->willReturn('tag,tag');
        $this->responseMock->expects(static::once())
            ->method('clearHeader')
            ->with('X-Magento-Tags');
        $this->responseMock->expects(static::once())
            ->method('setHeader')
            ->with('X-Magento-Tags', 'tag,' . CacheType::CACHE_TAG);
        $this->kernelMock->expects(static::once())
            ->method('process')
            ->with($this->responseMock);

        $this->assertSame(
            $this->resultMock,
            $this->plugin->afterRenderResult($this->resultMock, $this->resultMock, $this->responseMock)
        );
    }

    public function testAfterResultWithPluginDeveloperMode()
    {
        $this->registryMock->expects(static::once())
            ->method('registry')
            ->with('use_page_cache_plugin')
            ->willReturn(true);
        $this->stateMock->expects(static::once())
            ->method('getMode')
            ->willReturn(AppState::MODE_DEVELOPER);
        $this->httpHeaderMock->expects(static::any())
            ->method('getFieldValue')
            ->willReturnOnConsecutiveCalls('test', 'tag,tag2');
        $this->responseMock->expects(static::any())
            ->method('setHeader')
            ->willReturnCallback(function ($arg1, $arg2 = null, $arg3 = null) {
                if ($arg1 === 'X-Magento-Cache-Control' && $arg2 === 'test') {
                    return null;
                } elseif ($arg1 === 'X-Magento-Cache-Debug' && $arg2 === 'MISS' && $arg3 === true) {
                    return null;
                } elseif ($arg1 === 'X-Magento-Tags' && $arg2 === 'tag,tag2,' . CacheType::CACHE_TAG) {
                    return null;
                }
            });
        $this->responseMock->expects(static::once())
            ->method('clearHeader')
            ->with('X-Magento-Tags');
        $this->registryMock->expects(static::once())
            ->method('registry')
            ->with('use_page_cache_plugin')
            ->willReturn(true);
        $this->kernelMock->expects(static::once())
            ->method('process')
            ->with($this->responseMock);

        $this->assertSame(
            $this->resultMock,
            $this->plugin->afterRenderResult($this->resultMock, $this->resultMock, $this->responseMock)
        );
    }
}
