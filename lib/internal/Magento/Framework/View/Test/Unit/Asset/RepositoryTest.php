<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\View\Test\Unit\Asset;

use Magento\Framework\App\Request\Http;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Asset\ContextInterface;
use Magento\Framework\View\Asset\File;
use Magento\Framework\View\Asset\File\ContextFactory;
use Magento\Framework\View\Asset\File\FallbackContextFactory;
use Magento\Framework\View\Asset\FileFactory;
use Magento\Framework\View\Asset\RemoteFactory;
use Magento\Framework\View\Asset\Repository;
use Magento\Framework\View\Asset\RepositoryMap;
use Magento\Framework\View\Asset\Source;
use Magento\Framework\View\Design\Theme\ThemeProviderInterface;
use Magento\Framework\View\Design\ThemeInterface;
use Magento\Framework\View\DesignInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for Magento\Framework\View\Asset\Repository
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RepositoryTest extends TestCase
{
    /**
     * @var Repository
     */
    private $repository;

    /**
     * @var MockObject
     */
    protected $objectManagerMock;

    /**
     * @var UrlInterface|MockObject
     */
    private $urlMock;

    /**
     * @var DesignInterface|MockObject
     */
    private $designMock;

    /**
     * @var ThemeProviderInterface|MockObject
     */
    private $themeProvider;

    /**
     * @var Source|MockObject
     */
    private $sourceMock;

    /**
     * @var Http|MockObject
     */
    private $httpMock;

    /**
     * @var FileFactory|MockObject
     */
    private $fileFactoryMock;

    /**
     * @var FallbackContextFactory|MockObject
     */
    private $fallbackFactoryMock;

    /**
     * @var ContextFactory|MockObject
     */
    private $contextFactoryMock;

    /**
     * @var RemoteFactory|MockObject
     */
    private $remoteFactoryMock;

    /** @var ThemeInterface|MockObject */
    private $themeMock;

    /**
     * {@inheritDoc}
     */
    protected function setUp(): void
    {
        $this->objectManagerMock = $this->createPartialMock(
            \Magento\Framework\ObjectManager\ObjectManager::class,
            ['create', 'get']
        );
        $this->urlMock = $this->getMockBuilder(UrlInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->designMock = $this->getMockBuilder(DesignInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->themeProvider = $this->getMockForAbstractClass(ThemeProviderInterface::class);
        $this->sourceMock = $this->getMockBuilder(Source::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->httpMock = $this->getMockBuilder(Http::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->fileFactoryMock = $this->getMockBuilder(FileFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->fallbackFactoryMock = $this->getMockBuilder(
            FallbackContextFactory::class
        )
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->contextFactoryMock = $this->getMockBuilder(ContextFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->remoteFactoryMock = $this->getMockBuilder(RemoteFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $repositoryMapMock = $this->getMockBuilder(File::class)
            ->addMethods(['getMap'])
            ->disableOriginalConstructor()
            ->getMock();
        $repositoryMapMock->method('getMap')->willReturn([]);
        $this->objectManagerMock->method('get')
            ->with(RepositoryMap::class)
            ->willReturn($repositoryMapMock);
        \Magento\Framework\App\ObjectManager::setInstance($this->objectManagerMock);

        $this->designMock
            ->expects($this->any())
            ->method('getDesignParams')
            ->willReturn(
                [
                    'themeModel' => $this->getThemeMock(),
                    'area' => 'area',
                    'locale' => 'locale'
                ]
            );

        $this->repository = (new ObjectManager($this))->getObject(
            Repository::class,
            [
                'baseUrl' => $this->urlMock,
                'design' => $this->designMock,
                'themeProvider' => $this->themeProvider,
                'assetSource' => $this->sourceMock,
                'request' => $this->httpMock,
                'fileFactory' => $this->fileFactoryMock,
                'fallbackContextFactory' => $this->fallbackFactoryMock,
                'contextFactory' => $this->contextFactoryMock,
                'remoteFactory' => $this->remoteFactoryMock
            ]
        );
    }

    /**
     * @return void
     */
    public function testUpdateDesignParamsWrongTheme()
    {
        $this->expectException('UnexpectedValueException');
        $this->expectExceptionMessage('Could not find theme \'nonexistent_theme\' for area \'area\'');
        $params = ['area' => 'area', 'theme' => 'nonexistent_theme'];
        $this->themeProvider->expects($this->once())
            ->method('getThemeByFullPath')
            ->with('area/nonexistent_theme')
            ->willReturn(null);
        $this->repository->updateDesignParams($params);
    }

    /**
     * @param array $params
     * @param array $result
     * @return void
     * @dataProvider updateDesignParamsDataProvider
     */
    public function testUpdateDesignParams($params, $result)
    {
        $this->themeProvider
            ->expects($this->any())
            ->method('getThemeByFullPath')
            ->willReturn('ThemeID');

        $this->repository->updateDesignParams($params);
        $this->assertEquals($result, $params);
    }

    /**
     * @return void
     */
    public function testUpdateDesignParamsWithThemePath()
    {
        $params = ['area' => 'AREA'];
        $result = ['area' => 'AREA', 'themeModel' => 'Theme', 'module' => false, 'locale' => 'locale'];

        $this->designMock
            ->expects($this->once())
            ->method('getConfigurationDesignTheme')
            ->willReturn('themePath');

        $this->themeProvider
            ->expects($this->once())
            ->method('getThemeByFullPath')
            ->willReturn('Theme');

        $this->repository->updateDesignParams($params);
        $this->assertEquals($result, $params);
    }

    /**
     * @return void
     */
    public function testUpdateDesignParamsWithThemeId()
    {
        $params = ['area' => 'AREA'];
        $result = ['area' => 'AREA', 'themeModel' => 'Theme', 'module' => false, 'locale' => 'locale'];

        $this->designMock
            ->expects($this->once())
            ->method('getConfigurationDesignTheme')
            ->willReturn('1');

        $this->themeProvider
            ->expects($this->once())
            ->method('getThemeById')
            ->willReturn('Theme');

        $this->repository->updateDesignParams($params);
        $this->assertEquals($result, $params);
    }

    /**
     * @return array
     */
    public function updateDesignParamsDataProvider()
    {
        return [
            [
                ['area' => 'AREA'],
                ['area' => 'AREA', 'themeModel' => $this->getThemeMock(), 'module' => false, 'locale' => 'locale']],
            [
                ['themeId' => 'ThemeID'],
                ['area' => 'area', 'themeId' => 'ThemeID', 'themeModel' => 'ThemeID', 'module' => false, 'locale' => 'locale']
            ]
        ];
    }

    /**
     * @return void
     */
    public function testCreateAsset()
    {

        $this->themeProvider
            ->expects($this->any())
            ->method('getThemeByFullPath')
            ->willReturnArgument(0);

        $fallbackContextMock = $this->getMockBuilder(\Magento\Framework\View\Asset\File\FallbackContex::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->fallbackFactoryMock
            ->expects($this->once())
            ->method('create')
            ->with(
                [
                    'baseUrl' => null,
                    'areaType' => 'area',
                    'themePath' => 'Default',
                    'localeCode' => 'locale'
                ]
            )
            ->willReturn($fallbackContextMock);

        $assetMock = $this->getMockBuilder(File::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->fileFactoryMock
            ->expects($this->once())
            ->method('create')
            ->with(
                [
                    'source' => $this->sourceMock,
                    'context' => $fallbackContextMock,
                    'filePath' => 'test/file.js',
                    'module' => 'Test',
                    'contentType' => ''
                ]
            )
            ->willReturn($assetMock);

        $this->assertEquals(
            $assetMock,
            $this->repository->createAsset('test/file.js', ['module' => 'Test', 'theme' => 'Default'])
        );
    }

    /**
     * @return void
     */
    public function testGetStaticViewFileContext()
    {
        $themeMock = $this->getMockForAbstractClass(ThemeInterface::class);
        $this->designMock
            ->expects($this->any())
            ->method('getDesignParams')
            ->willReturn(
                [
                    'themeModel' => $themeMock,
                    'area' => 'area',
                    'locale' => 'locale'
                ]
            );
        $this->themeProvider
            ->expects($this->any())
            ->method('getThemeByFullPath')
            ->willReturnArgument(0);
        $this->httpMock
            ->expects($this->any())
            ->method('isSecure')
            ->willReturn(false);

        $fallbackContextMock = $this->getMockBuilder(\Magento\Framework\View\Asset\File\FallbackContex::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->fallbackFactoryMock
            ->expects($this->once())
            ->method('create')
            ->with(
                [
                    'baseUrl' => '',
                    'areaType' => 'area',
                    'themePath' => '',
                    'localeCode' => 'locale'
                ]
            )
            ->willReturn($fallbackContextMock);

        $this->assertEquals(
            $fallbackContextMock,
            $this->repository->getStaticViewFileContext()
        );
    }

    /**
     * @param string $filePath
     * @param string $resultFilePath
     * @param string $module
     * @return void
     * @dataProvider createRelatedDataProvider
     */
    public function testCreateRelated($filePath, $resultFilePath, $module)
    {
        $originalContextMock = $this->getMockBuilder(ContextInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $originalAssetMock = $this->getMockBuilder(File::class)
            ->disableOriginalConstructor()
            ->setMethods(['getModule', 'getContext'])
            ->getMock();
        $originalAssetMock
            ->expects($this->any())
            ->method('getContext')
            ->willReturn($originalContextMock);

        $assetMock = $this->getMockBuilder(File::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->fileFactoryMock
            ->expects($this->once())
            ->method('create')
            ->with(
                [
                    'source' => $this->sourceMock,
                    'context' => $originalContextMock,
                    'filePath' => $resultFilePath,
                    'module' => $module,
                    'contentType' => ''
                ]
            )
            ->willReturn($assetMock);

        $this->assertEquals(
            $assetMock,
            $this->repository->createRelated($filePath, $originalAssetMock)
        );
    }

    /**
     * @return array
     */
    public function createRelatedDataProvider()
    {
        return [
            ['test/file.js', '/test/file.js', ''],
            ['test::file.js', 'file.js', 'test'],
        ];
    }

    /**
     * @return void
     */
    public function testCreateArbitrary()
    {
        $contextMock = $this->getMockBuilder(ContextInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->contextFactoryMock
            ->expects($this->once())
            ->method('create')
            ->with(
                [
                    'baseUrl' => '',
                    'baseDirType' => 'dirType',
                    'contextPath' => 'dir/path'
                ]
            )
            ->willReturn($contextMock);

        $assetMock = $this->getMockBuilder(File::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->fileFactoryMock
            ->expects($this->once())
            ->method('create')
            ->with(
                [
                    'source' => $this->sourceMock,
                    'context' => $contextMock,
                    'filePath' => 'test/file.js',
                    'module' => '',
                    'contentType' => ''
                ]
            )
            ->willReturn($assetMock);

        $this->assertEquals(
            $assetMock,
            $this->repository->createArbitrary('test/file.js', 'dir/path', 'dirType', 'static')
        );
    }

    /**
     * @return void
     */
    public function testGetUrl()
    {
        $themeMock = $this->getMockForAbstractClass(ThemeInterface::class);
        $this->designMock
            ->expects($this->any())
            ->method('getDesignParams')
            ->willReturn(
                [
                    'themeModel' => $themeMock,
                    'area' => 'area',
                    'locale' => 'locale'
                ]
            );

        $assetMock = $this->getMockBuilder(File::class)
            ->disableOriginalConstructor()
            ->getMock();
        $assetMock
            ->expects($this->any())
            ->method('getUrl')
            ->willReturn('some url');

        $this->fileFactoryMock
            ->expects($this->exactly(2))
            ->method('create')
            ->with(
                [
                    'source' => $this->sourceMock,
                    'context' => '',
                    'filePath' => 'test/file.js',
                    'module' => '',
                    'contentType' => ''
                ]
            )
            ->willReturn($assetMock);

        $this->assertEquals(
            'some url',
            $this->repository->getUrl('test/file.js')
        );
        $this->assertEquals(
            'some url',
            $this->repository->getUrlWithParams('test/file.js', [])
        );
    }

    /**
     * @return void
     */
    public function testExtractModuleException()
    {
        $this->expectException('Magento\Framework\Exception\LocalizedException');
        $this->expectExceptionMessage('Scope separator "::" cannot be used without scope identifier.');
        $this->repository->extractModule('::asdsad');
    }

    /**
     * @return ThemeInterface|MockObject
     */
    private function getThemeMock()
    {
        if (null === $this->themeMock) {
            $this->themeMock = $this->getMockForAbstractClass(ThemeInterface::class);
        }

        return $this->themeMock;
    }
}
