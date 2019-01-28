<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\PageCache\Test\Unit\Model\Controller\Result;

use Magento\PageCache\Model\Controller\Result\BuiltinPlugin;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\PageCache\Model\Config;
use Magento\Framework\App\PageCache\Kernel;
use Magento\Framework\App\State as AppState;
use Magento\Framework\Registry;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\App\Response\Http as ResponseHttp;
use Zend\Http\Header\HeaderInterface as HttpHeaderInterface;
use Magento\PageCache\Model\Cache\Type as CacheType;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class BuiltinPluginTest extends \PHPUnit\Framework\TestCase
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
     * @var Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configMock;

    /**
     * @var Kernel|\PHPUnit_Framework_MockObject_MockObject
     */
    private $kernelMock;

    /**
     * @var AppState|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stateMock;

    /**
     * @var Registry|\PHPUnit_Framework_MockObject_MockObject
     */
    private $registryMock;

    /**
     * @var ResultInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultMock;

    /**
     * @var ResponseHttp|\PHPUnit_Framework_MockObject_MockObject
     */
    private $responseMock;

    /**
     * @var HttpHeaderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $httpHeaderMock;

    protected function setUp()
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

        $this->responseMock->expects($this->any())
            ->method('getHeader')
            ->willReturnMap(
                [
                    ['X-Magento-Tags', $this->httpHeaderMock],
                    ['Cache-Control', $this->httpHeaderMock]
                ]
            );
        $this->configMock->expects($this->any())
            ->method('isEnabled')
            ->willReturn(true);
        $this->configMock->expects($this->any())
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
        $this->registryMock->expects($this->once())
            ->method('registry')
            ->with('use_page_cache_plugin')
            ->willReturn(false);
        $this->kernelMock->expects($this->never())
            ->method('process')
            ->with($this->responseMock);

        $this->assertSame(
            $this->resultMock,
            $this->plugin->afterRenderResult($this->resultMock, $this->resultMock, $this->responseMock)
        );
    }

    public function testAfterResultWithPlugin()
    {
        $this->registryMock->expects($this->once())
            ->method('registry')
            ->with('use_page_cache_plugin')
            ->willReturn(true);
        $this->stateMock->expects($this->once())
            ->method('getMode')
            ->willReturn(null);
        $this->httpHeaderMock->expects($this->any())
            ->method('getFieldValue')
            ->willReturn('tag,tag');
        $this->responseMock->expects($this->once())
            ->method('clearHeader')
            ->with('X-Magento-Tags');
        $this->responseMock->expects($this->once())
            ->method('setHeader')
            ->with('X-Magento-Tags', 'tag,' . CacheType::CACHE_TAG);
        $this->kernelMock->expects($this->once())
            ->method('process')
            ->with($this->responseMock);

        $this->assertSame(
            $this->resultMock,
            $this->plugin->afterRenderResult($this->resultMock, $this->resultMock, $this->responseMock)
        );
    }

    public function testAfterResultWithPluginDeveloperMode()
    {
        $this->registryMock->expects($this->once())
            ->method('registry')
            ->with('use_page_cache_plugin')
            ->willReturn(true);
        $this->stateMock->expects($this->once())
            ->method('getMode')
            ->willReturn(AppState::MODE_DEVELOPER);
        $this->httpHeaderMock->expects($this->any())
            ->method('getFieldValue')
            ->willReturnOnConsecutiveCalls('test', 'tag,tag2');
        $this->responseMock->expects($this->any())
            ->method('setHeader')
            ->withConsecutive(
                ['X-Magento-Cache-Control', 'test'],
                ['X-Magento-Cache-Debug', 'MISS', true],
                ['X-Magento-Tags', 'tag,tag2,' . CacheType::CACHE_TAG]
            );
        $this->responseMock->expects($this->once())
            ->method('clearHeader')
            ->with('X-Magento-Tags');
        $this->registryMock->expects($this->once())
            ->method('registry')
            ->with('use_page_cache_plugin')
            ->willReturn(true);
        $this->kernelMock->expects($this->once())
            ->method('process')
            ->with($this->responseMock);

        $this->assertSame(
            $this->resultMock,
            $this->plugin->afterRenderResult($this->resultMock, $this->resultMock, $this->responseMock)
        );
    }
}
