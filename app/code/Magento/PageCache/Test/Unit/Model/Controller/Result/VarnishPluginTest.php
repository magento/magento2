<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\PageCache\Test\Unit\Model\Controller\Result;

use Magento\Framework\App\PageCache\Version;
use Magento\Framework\App\Response\Http as ResponseHttp;
use Magento\Framework\App\State as AppState;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Registry;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\PageCache\Model\Config;
use Magento\PageCache\Model\Controller\Result\VarnishPlugin;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class VarnishPluginTest extends TestCase
{
    /**
     * @var VarnishPlugin
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
     * @var Version|MockObject
     */
    private $versionMock;

    /**
     * @var AppState|MockObject
     */
    private $appStateMock;

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

    protected function setUp(): void
    {
        $this->configMock = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->versionMock = $this->getMockBuilder(Version::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->appStateMock = $this->getMockBuilder(AppState::class)
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

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->plugin = $this->objectManagerHelper->getObject(
            VarnishPlugin::class,
            [
                'registry' => $this->registryMock,
                'config' => $this->configMock,
                'state' => $this->appStateMock,
                'version' => $this->versionMock
            ]
        );
    }

    /**
     * @param bool $usePlugin
     * @param int $setCacheDebugHeaderCount
     * @param int $getModeCount
     * @param int $processCount
     *
     * @dataProvider afterRenderResultDataProvider
     */
    public function testAfterRenderResult($usePlugin, $setCacheDebugHeaderCount, $getModeCount, $processCount)
    {
        $this->responseMock->expects(static::exactly($setCacheDebugHeaderCount))
            ->method('setHeader')
            ->with('X-Magento-Debug', 1);
        $this->registryMock->expects(static::once())
            ->method('registry')
            ->with('use_page_cache_plugin')
            ->willReturn($usePlugin);
        $this->configMock->expects(static::once())
            ->method('isEnabled')
            ->willReturn(true);
        $this->configMock->expects(static::once())
            ->method('getType')
            ->willReturn(Config::VARNISH);
        $this->appStateMock->expects(static::exactly($getModeCount))
            ->method('getMode')
            ->willReturn(AppState::MODE_DEVELOPER);
        $this->versionMock->expects(static::exactly($processCount))
            ->method('process');

        $this->assertSame(
            $this->resultMock,
            $this->plugin->afterRenderResult($this->resultMock, $this->resultMock, $this->responseMock)
        );
    }

    /**
     * @return array
     */
    public static function afterRenderResultDataProvider()
    {
        return [
            [true, 1, 1, 1],
            [false, 0, 0, 0]
        ];
    }
}
