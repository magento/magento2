<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App\Test\Unit;

use Magento\Framework\App\Bootstrap;
use Magento\Framework\Filesystem;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class StaticResourceTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\App\State|\PHPUnit_Framework_MockObject_MockObject
     */
    private $state;

    /**
     * @var \Magento\Framework\App\Response\FileInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $response;

    /**
     * @var \Magento\Framework\App\Request\Http|\PHPUnit_Framework_MockObject_MockObject
     */
    private $request;

    /**
     * @var \Magento\Framework\App\View\Asset\Publisher|\PHPUnit_Framework_MockObject_MockObject
     */
    private $publisher;

    /**
     * @var \Magento\Framework\View\Asset\Repository|\PHPUnit_Framework_MockObject_MockObject
     */
    private $assetRepo;

    /**
     * @var \Magento\Framework\Module\ModuleList|\PHPUnit_Framework_MockObject_MockObject
     */
    private $moduleList;

    /**
     * @var \Magento\Framework\ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $objectManager;

    /**
     * @var \Magento\Framework\App\ObjectManager\ConfigLoader|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configLoader;

    /**
     * @var \Magento\Framework\App\StaticResource
     */
    private $object;

    /**
     * @var \Psr\Log\LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $logger;

    protected function setUp()
    {
        $this->state = $this->createMock(\Magento\Framework\App\State::class);
        $this->response = $this->createMock(\Magento\MediaStorage\Model\File\Storage\Response::class);
        $this->request = $this->createMock(\Magento\Framework\App\Request\Http::class);
        $this->publisher = $this->createMock(\Magento\Framework\App\View\Asset\Publisher::class);
        $this->assetRepo = $this->createMock(\Magento\Framework\View\Asset\Repository::class);
        $this->moduleList = $this->createMock(\Magento\Framework\Module\ModuleList::class);
        $this->objectManager = $this->getMockForAbstractClass(\Magento\Framework\ObjectManagerInterface::class);
        $this->logger = $this->getMockForAbstractClass(\Psr\Log\LoggerInterface::class);
        $this->configLoader = $this->createMock(\Magento\Framework\App\ObjectManager\ConfigLoader::class);
        $this->object = new \Magento\Framework\App\StaticResource(
            $this->state,
            $this->response,
            $this->request,
            $this->publisher,
            $this->assetRepo,
            $this->moduleList,
            $this->objectManager,
            $this->configLoader,
            $this->getMockForAbstractClass(\Magento\Framework\View\DesignInterface::class)
        );
    }

    public function testLaunchProductionMode()
    {
        $this->state->expects($this->once())
            ->method('getMode')
            ->will($this->returnValue(\Magento\Framework\App\State::MODE_PRODUCTION));
        $this->response->expects($this->once())
            ->method('setHttpResponseCode')
            ->with(404);
        $this->response->expects($this->never())
            ->method('setFilePath');
        $this->object->launch();
    }

    /**
     * @param string $mode
     * @param string $requestedPath
     * @param string $requestedModule
     * @param bool $moduleExists
     * @param string $expectedFile
     * @param array $expectedParams
     *
     * @dataProvider launchDataProvider
     */
    public function testLaunch(
        $mode,
        $requestedPath,
        $requestedModule,
        $moduleExists,
        $expectedFile,
        array $expectedParams
    ) {
        $this->state->expects($this->once())
            ->method('getMode')
            ->will($this->returnValue($mode));
        $this->state->expects($this->once())
            ->method('setAreaCode')
            ->with('area');
        $this->configLoader->expects($this->once())
            ->method('load')
            ->with('area')
            ->will($this->returnValue(['config']));
        $this->objectManager->expects($this->once())
            ->method('configure')
            ->with(['config']);
        $this->request->expects($this->once())
            ->method('get')
            ->with('resource')
            ->will($this->returnValue($requestedPath));
        $this->moduleList->expects($this->any())
            ->method('has')
            ->with($requestedModule)
            ->will($this->returnValue($moduleExists));
        $asset = $this->getMockForAbstractClass(\Magento\Framework\View\Asset\LocalInterface::class);
        $asset->expects($this->once())->method('getSourceFile')->will($this->returnValue('resource/file.css'));
        $this->assetRepo->expects($this->once())
            ->method('createAsset')
            ->with($expectedFile, $expectedParams)
            ->will($this->returnValue($asset));
        $this->publisher->expects($this->once())->method('publish')->with($asset);
        $this->response->expects($this->once())
            ->method('setFilePath')
            ->with('resource/file.css');
        $this->object->launch();
    }

    /**
     * @return array
     */
    public function launchDataProvider()
    {
        return [
            'developer mode with non-modular resource' => [
                \Magento\Framework\App\State::MODE_DEVELOPER,
                'area/Magento/theme/locale/dir/file.js',
                'dir',
                false,
                'dir/file.js',
                ['area' => 'area', 'locale' => 'locale', 'module' => '', 'theme' => 'Magento/theme'],
            ],
            'default mode with modular resource' => [
                \Magento\Framework\App\State::MODE_DEFAULT,
                'area/Magento/theme/locale/Namespace_Module/dir/file.js',
                'Namespace_Module',
                true,
                'dir/file.js',
                [
                    'area' => 'area', 'locale' => 'locale', 'module' => 'Namespace_Module', 'theme' => 'Magento/theme'
                ],
            ],
        ];
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Requested path 'short/path.js' is wrong
     */
    public function testLaunchWrongPath()
    {
        $this->state->expects($this->once())
            ->method('getMode')
            ->will($this->returnValue(\Magento\Framework\App\State::MODE_DEVELOPER));
        $this->request->expects($this->once())
            ->method('get')
            ->with('resource')
            ->will($this->returnValue('short/path.js'));
        $this->object->launch();
    }

    public function testCatchExceptionDeveloperMode()
    {
        $this->objectManager->expects($this->once())
            ->method('get')
            ->with(\Psr\Log\LoggerInterface::class)
            ->willReturn($this->logger);
        $this->logger->expects($this->once())
            ->method('critical');
        $bootstrap = $this->getMockBuilder(Bootstrap::class)->disableOriginalConstructor()->getMock();
        $bootstrap->expects($this->once())->method('isDeveloperMode')->willReturn(true);
        $exception = new \Exception('Error: nothing works');
        $this->response->expects($this->once())->method('setHttpResponseCode')->with(404);
        $this->response->expects($this->once())->method('sendResponse');
        $this->assertTrue($this->object->catchException($bootstrap, $exception));
    }
}
