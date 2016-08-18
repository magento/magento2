<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\PageCache\Test\Unit\Model\App\FrontController;

use Magento\PageCache\Model\App\FrontController\VarnishPlugin;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\PageCache\Model\Config;
use Magento\Framework\App\PageCache\Version;
use Magento\Framework\App\State as AppState;
use Magento\Framework\App\FrontControllerInterface;
use Magento\Framework\App\Response\Http as ResponseHttp;
use Magento\Framework\Controller\ResultInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class VarnishPluginTest extends \PHPUnit_Framework_TestCase
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
     * @var Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configMock;

    /**
     * @var Version|\PHPUnit_Framework_MockObject_MockObject
     */
    private $versionMock;

    /**
     * @var AppState|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stateMock;

    /**
     * @var FrontControllerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $frontControllerMock;

    /**
     * @var ResponseHttp|\PHPUnit_Framework_MockObject_MockObject
     */
    private $responseMock;

    /**
     * @var ResultInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultMock;

    protected function setUp()
    {
        $this->configMock = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->versionMock = $this->getMockBuilder(Version::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->stateMock = $this->getMockBuilder(AppState::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->frontControllerMock = $this->getMockBuilder(FrontControllerInterface::class)
            ->getMockForAbstractClass();
        $this->responseMock = $this->getMockBuilder(ResponseHttp::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultMock = $this->getMockBuilder(ResultInterface::class)
            ->getMockForAbstractClass();

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->plugin = $this->objectManagerHelper->getObject(
            VarnishPlugin::class,
            [
                'config' => $this->configMock,
                'version' => $this->versionMock,
                'state' => $this->stateMock
            ]
        );
    }

    /**
     * @param string $state
     * @param int $countHeader
     *
     * @dataProvider afterDispatchDataProvider
     */
    public function testAfterDispatchReturnsCache($state, $countHeader)
    {
        $this->configMock->expects(static::once())
            ->method('isEnabled')
            ->willReturn(true);
        $this->configMock->expects(static::once())
            ->method('getType')
            ->willReturn(Config::VARNISH);
        $this->versionMock->expects(static::once())
            ->method('process');
        $this->stateMock->expects(static::once())
            ->method('getMode')
            ->willReturn($state);
        $this->responseMock->expects(static::exactly($countHeader))
            ->method('setHeader')
            ->with('X-Magento-Debug');

        $this->assertSame(
            $this->responseMock,
            $this->plugin->afterDispatch($this->frontControllerMock, $this->responseMock)
        );
    }

    public function testAfterDispatchNotResponse()
    {
        $this->configMock->expects(static::once())
            ->method('isEnabled')
            ->willReturn(true);
        $this->configMock->expects(static::once())
            ->method('getType')
            ->willReturn(Config::VARNISH);
        $this->versionMock->expects(static::never())
            ->method('process');
        $this->stateMock->expects(static::never())
            ->method('getMode');
        $this->resultMock->expects(static::never())
            ->method('setHeader');

        $this->assertSame(
            $this->resultMock,
            $this->plugin->afterDispatch($this->frontControllerMock, $this->resultMock)
        );
    }

    public function testAfterDispatchDisabled()
    {
        $this->configMock->expects(static::any())
            ->method('getType')
            ->willReturn(null);
        $this->versionMock->expects(static::never())
            ->method('process');
        $this->stateMock->expects(static::any())
            ->method('getMode')
            ->willReturn(AppState::MODE_DEVELOPER);
        $this->responseMock->expects(static::never())
            ->method('setHeader');

        $this->assertSame(
            $this->responseMock,
            $this->plugin->afterDispatch($this->frontControllerMock, $this->responseMock)
        );
    }

    /**
     * @return array
     */
    public function afterDispatchDataProvider()
    {
        return [
            'developer_mode' => [AppState::MODE_DEVELOPER, 1],
            'production' => [AppState::MODE_PRODUCTION, 0]
        ];
    }
}
