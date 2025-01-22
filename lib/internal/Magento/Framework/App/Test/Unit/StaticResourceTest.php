<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\App\Test\Unit;

use Magento\Framework\App\Bootstrap;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\ObjectManager\ConfigLoader;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\App\Response\FileInterface;
use Magento\Framework\App\State;
use Magento\Framework\App\StaticResource;
use Magento\Framework\App\View\Asset\Publisher;
use Magento\Framework\Config\ConfigOptionsListConstants;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Module\ModuleList;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Validator\Locale;
use Magento\Framework\View\Asset\LocalInterface;
use Magento\Framework\View\Asset\Repository;
use Magento\Framework\View\Design\Theme\ThemePackageList;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class StaticResourceTest extends TestCase
{
    /**
     * @var State|MockObject
     */
    private $stateMock;

    /**
     * @var FileInterface|MockObject
     */
    private $responseMock;

    /**
     * @var HttpRequest|MockObject
     */
    private $requestMock;

    /**
     * @var Publisher|MockObject
     */
    private $publisherMock;

    /**
     * @var Repository|MockObject
     */
    private $assetRepoMock;

    /**
     * @var ModuleList|MockObject
     */
    private $moduleListMock;

    /**
     * @var ObjectManagerInterface|MockObject
     */
    private $objectManagerMock;

    /**
     * @var ConfigLoader|MockObject
     */
    private $configLoaderMock;

    /**
     * @var LoggerInterface|MockObject
     */
    private $loggerMock;

    /**
     * @var DeploymentConfig|MockObject
     */
    private $deploymentConfigMock;

    /**
     * @var File|MockObject
     */
    private $driverMock;

    /**
     * @var ThemePackageList|MockObject
     */
    private $themePackageListMock;

    /**
     * @var Locale|MockObject
     */
    private $localeValidatorMock;

    /**
     * @var StaticResource
     */
    private $object;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->stateMock = $this->createMock(State::class);
        $this->responseMock = $this->getMockForAbstractClass(FileInterface::class);
        $this->requestMock = $this->createMock(HttpRequest::class);
        $this->publisherMock = $this->createMock(Publisher::class);
        $this->assetRepoMock = $this->createMock(Repository::class);
        $this->moduleListMock = $this->createMock(ModuleList::class);
        $this->objectManagerMock = $this->getMockForAbstractClass(ObjectManagerInterface::class);
        $this->loggerMock = $this->getMockForAbstractClass(LoggerInterface::class);
        $this->configLoaderMock = $this->createMock(ConfigLoader::class);
        $this->deploymentConfigMock = $this->createMock(DeploymentConfig::class);
        $this->driverMock = $this->createMock(File::class);
        $this->themePackageListMock = $this->createMock(ThemePackageList::class);
        $this->localeValidatorMock = $this->createMock(Locale::class);
        $this->object = new StaticResource(
            $this->stateMock,
            $this->responseMock,
            $this->requestMock,
            $this->publisherMock,
            $this->assetRepoMock,
            $this->moduleListMock,
            $this->objectManagerMock,
            $this->configLoaderMock,
            $this->deploymentConfigMock,
            $this->driverMock,
            $this->themePackageListMock,
            $this->localeValidatorMock
        );
    }

    /**
     * Test to lunch on production mode
     */
    public function testLaunchProductionMode()
    {
        $this->stateMock->expects($this->once())
            ->method('getMode')
            ->willReturn(State::MODE_PRODUCTION);
        $this->responseMock->expects($this->once())
            ->method('setHttpResponseCode')
            ->with(404);
        $this->responseMock->expects($this->never())
            ->method('setFilePath');
        $this->stateMock->expects($this->never())->method('setAreaCode');
        $this->configLoaderMock->expects($this->never())->method('load');
        $this->objectManagerMock->expects($this->never())->method('configure');
        $this->requestMock->expects($this->never())->method('get');
        $this->moduleListMock->expects($this->never())->method('has');
        $asset = $this->getMockForAbstractClass(LocalInterface::class);
        $asset->expects($this->never())->method('getSourceFile');
        $this->assetRepoMock->expects($this->never())->method('createAsset');
        $this->publisherMock->expects($this->never())->method('publish');
        $this->responseMock->expects($this->never())->method('setFilePath');
        $this->object->launch();
    }

    /**
     * @param string $mode
     * @param string $requestedPath
     * @param string $requestedModule
     * @param bool $moduleExists
     * @param string $expectedFile
     * @param array $expectedParams
     * @param int $getConfigDataExpects
     * @param int $staticContentOmDemandInProduction
     *
     * @dataProvider launchDataProvider
     */
    public function testLaunch(
        $mode,
        $requestedPath,
        $requestedModule,
        $moduleExists,
        $expectedFile,
        array $expectedParams,
        $getConfigDataExpects,
        $staticContentOmDemandInProduction
    ) {
        $this->deploymentConfigMock->expects($this->exactly($getConfigDataExpects))
            ->method('getConfigData')
            ->with(ConfigOptionsListConstants::CONFIG_PATH_SCD_ON_DEMAND_IN_PRODUCTION)
            ->willReturn($staticContentOmDemandInProduction);
        $this->stateMock->expects($this->once())
            ->method('getMode')
            ->willReturn($mode);
        $this->stateMock->expects($this->once())
            ->method('setAreaCode')
            ->with('area');
        $this->configLoaderMock->expects($this->once())
            ->method('load')
            ->with('area')
            ->willReturn(['config']);
        $this->objectManagerMock->expects($this->once())
            ->method('configure')
            ->with(['config']);
        $this->requestMock->expects($this->once())
            ->method('get')
            ->with('resource')
            ->willReturn($requestedPath);
        $this->moduleListMock->expects($this->any())
            ->method('has')
            ->with($requestedModule)
            ->willReturn($moduleExists);
        $asset = $this->getMockForAbstractClass(LocalInterface::class);
        $asset->expects($this->once())
            ->method('getSourceFile')
            ->willReturn('resource/file.css');
        $this->assetRepoMock->expects($this->once())
            ->method('createAsset')
            ->with($expectedFile, $expectedParams)
            ->willReturn($asset);
        $this->publisherMock->expects($this->once())
            ->method('publish')
            ->with($asset);
        $this->responseMock->expects($this->once())
            ->method('setFilePath')
            ->with('resource/file.css');
        $this->driverMock->expects($this->once())
            ->method('getRealPathSafety')
            ->willReturnArgument(0);
        $this->themePackageListMock->expects($this->atLeastOnce())->method('getThemes')->willReturn(
            [
                'area/Magento/theme' => [
                    'area' => 'area',
                    'vendor' => 'Magento',
                    'name' => 'theme',
                ],
            ],
        );
        $this->localeValidatorMock->expects($this->once())->method('isValid')->willReturn(true);
        $this->object->launch();
    }

    /**
     * @return array
     */
    public static function launchDataProvider()
    {
        return [
            'developer mode with non-modular resource' => [
                State::MODE_DEVELOPER,
                'area/Magento/theme/locale/dir/file.js',
                'dir',
                false,
                'dir/file.js',
                ['area' => 'area', 'locale' => 'locale', 'module' => '', 'theme' => 'Magento/theme'],
                0,
                0,
            ],
            'default mode with modular resource' => [
                State::MODE_DEFAULT,
                'area/Magento/theme/locale/Namespace_Module/dir/file.js',
                'Namespace_Module',
                true,
                'dir/file.js',
                [
                    'area' => 'area', 'locale' => 'locale', 'module' => 'Namespace_Module', 'theme' => 'Magento/theme'
                ],
                0,
                0,
            ],
            'production mode with static_content_on_demand_in_production and with non-modular resource' => [
                State::MODE_PRODUCTION,
                'area/Magento/theme/locale/dir/file.js',
                'dir',
                false,
                'dir/file.js',
                ['area' => 'area', 'locale' => 'locale', 'module' => '', 'theme' => 'Magento/theme'],
                1,
                1,
            ],
            'production mode with static_content_on_demand_in_production and with modular resource' => [
                State::MODE_PRODUCTION,
                'area/Magento/theme/locale/Namespace_Module/dir/file.js',
                'Namespace_Module',
                true,
                'dir/file.js',
                [
                    'area' => 'area', 'locale' => 'locale', 'module' => 'Namespace_Module', 'theme' => 'Magento/theme'
                ],
                1,
                1,
            ],
        ];
    }

    /**
     * Test to lunch with wrong path on developer mode
     */
    public function testLaunchWrongPath()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('Requested path \'short/path.js\' is wrong');
        $this->stateMock->expects($this->once())
            ->method('getMode')
            ->willReturn(State::MODE_DEVELOPER);
        $this->requestMock->expects($this->once())
            ->method('get')
            ->with('resource')
            ->willReturn('short/path.js');
        $this->driverMock->expects($this->once())
            ->method('getRealPathSafety')
            ->willReturnArgument(0);
        $this->object->launch();
    }

    /**
     * Test to lunch with wrong path on production mode
     */
    public function testLaunchWrongPathProductionMode()
    {
        $mode = State::MODE_PRODUCTION;
        $path = 'wrong/path.js';

        $this->stateMock->method('getMode')->willReturn($mode);
        $this->deploymentConfigMock->method('getConfigData')
            ->with(ConfigOptionsListConstants::CONFIG_PATH_SCD_ON_DEMAND_IN_PRODUCTION)
            ->willReturn(true);
        $this->requestMock->method('get')->with('resource')->willReturn($path);
        $this->responseMock->expects($this->once())
            ->method('setHttpResponseCode')
            ->with(404);
        $this->driverMock->expects($this->once())
            ->method('getRealPathSafety')
            ->willReturnArgument(0);
        $this->object->launch();
    }

    /**
     * Test to Ability to handle exceptions on developer mode
     */
    public function testCatchExceptionDeveloperMode()
    {
        $this->objectManagerMock->expects($this->once())
            ->method('get')
            ->with(LoggerInterface::class)
            ->willReturn($this->loggerMock);
        $this->loggerMock->expects($this->once())
            ->method('critical');
        $bootstrap = $this->getMockBuilder(Bootstrap::class)
            ->disableOriginalConstructor()
            ->getMock();
        $bootstrap->expects($this->once())
            ->method('isDeveloperMode')
            ->willReturn(true);
        $exception = new \Exception('Error: nothing works');
        $this->responseMock->expects($this->once())
            ->method('setHttpResponseCode')
            ->with(404);
        $this->responseMock->expects($this->once())
            ->method('sendResponse');
        $this->assertTrue($this->object->catchException($bootstrap, $exception));
    }

    /**
     * Test to lunch with wrong path
     */
    public function testLaunchPathAbove()
    {
        $this->expectException('InvalidArgumentException');
        $path = 'frontend/..\..\folder_above/././Magento_Ui/template/messages.html';
        $this->stateMock->expects($this->once())
            ->method('getMode')
            ->willReturn(State::MODE_DEVELOPER);
        $this->requestMock->expects($this->once())
            ->method('get')
            ->with('resource')
            ->willReturn('frontend/..\..\folder_above/././Magento_Ui/template/messages.html');
        $this->driverMock->expects($this->once())
            ->method('getRealPathSafety')
            ->with('frontend/..\..\folder_above/././Magento_Ui/template/messages.html')
            ->willReturn('folder_above/Magento_Ui/template/messages.html');
        $this->expectExceptionMessage("Requested path '$path' is wrong.");

        $this->object->launch();
    }

    /**
     * @param array $themes
     * @dataProvider themesDataProvider
     */
    public function testLaunchWithInvalidTheme(array $themes): void
    {
        $this->expectException('InvalidArgumentException');
        $path = 'frontend/Test/luma/en_US/calendar.css';

        $this->stateMock->expects($this->once())
            ->method('getMode')
            ->willReturn(State::MODE_DEVELOPER);
        $this->requestMock->expects($this->once())
            ->method('get')
            ->with('resource')
            ->willReturn($path);
        $this->driverMock->expects($this->once())
            ->method('getRealPathSafety')
            ->with($path)
            ->willReturn($path);
        $this->themePackageListMock->expects($this->once())->method('getThemes')->willReturn($themes);
        $this->localeValidatorMock->expects($this->never())->method('isValid');
        $this->expectExceptionMessage('Requested path ' . $path . ' is wrong.');

        $this->object->launch();
    }

    /**
     * @param array $themes
     * @dataProvider themesDataProvider
     */
    public function testLaunchWithInvalidLocale(array $themes): void
    {
        $this->expectException('InvalidArgumentException');
        $path = 'frontend/Magento/luma/test/calendar.css';

        $this->stateMock->expects($this->once())
            ->method('getMode')
            ->willReturn(State::MODE_DEVELOPER);
        $this->requestMock->expects($this->once())
            ->method('get')
            ->with('resource')
            ->willReturn($path);
        $this->driverMock->expects($this->once())
            ->method('getRealPathSafety')
            ->with($path)
            ->willReturn($path);
        $this->themePackageListMock->expects($this->once())->method('getThemes')->willReturn($themes);
        $this->localeValidatorMock->expects($this->once())->method('isValid')->willReturn(false);
        $this->expectExceptionMessage('Requested path ' . $path . ' is wrong.');

        $this->object->launch();
    }

    /**
     * @return array
     */
    public static function themesDataProvider(): array
    {
        return  [
            [
                [
                    'adminhtml/Magento/backend' => [
                        'area' => 'adminhtml',
                        'vendor' => 'Magento',
                        'name' => 'backend',
                    ],
                    'frontend/Magento/blank' => [
                        'area' => 'frontend',
                        'vendor' => 'Magento',
                        'name' => 'blank',
                    ],
                    'frontend/Magento/luma' => [
                        'area' => 'frontend',
                        'vendor' => 'Magento',
                        'name' => 'luma',
                    ],
                ],
            ],
        ];
    }
}
