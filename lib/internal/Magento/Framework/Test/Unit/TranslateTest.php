<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Test\Unit;

use Magento\Framework\App\Language\Dictionary;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ScopeResolverInterface;
use Magento\Framework\App\State;
use Magento\Framework\Cache\FrontendInterface;
use Magento\Framework\DataObject;
use Magento\Framework\File\Csv;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\ReadInterface;
use Magento\Framework\Filesystem\DriverInterface;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Module\Dir\Reader;
use Magento\Framework\Module\ModuleList;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Translate;
use Magento\Framework\Translate\ResourceInterface;
use Magento\Framework\View\DesignInterface;
use Magento\Framework\View\FileSystem as FilesystemView;
use Magento\Theme\Model\Theme;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class TranslateTest extends TestCase
{
    /**
     * @var Translate
     */
    protected $translate;

    /**
     * @var DesignInterface|MockObject
     */
    protected $viewDesign;

    /**
     * @var FrontendInterface|MockObject
     */
    protected $cache;

    /**
     * @var FilesystemView|MockObject
     */
    protected $viewFileSystem;

    /**
     * @var ModuleList|MockObject
     */
    protected $moduleList;

    /**
     * @var Reader|MockObject
     */
    protected $modulesReader;

    /**
     * @var ScopeResolverInterface|MockObject
     */
    protected $scopeResolver;

    /**
     * @var ResourceInterface|MockObject
     */
    protected $resource;

    /**
     * @var ResolverInterface|MockObject
     */
    protected $locale;

    /**
     * @var State|MockObject
     */
    protected $appState;

    /**
     * @var Filesystem|MockObject
     */
    protected $filesystem;

    /**
     * @var RequestInterface|MockObject
     */
    protected $request;

    /**
     * @var Csv|MockObject
     */
    protected $csvParser;

    /**
     * @var  Dictionary|MockObject
     */
    protected $packDictionary;

    /**
     * @var ReadInterface|MockObject
     */
    protected $directory;

    /**
     * @var DriverInterface|MockObject
     */
    protected $fileDriver;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->viewDesign = $this->getMockForAbstractClass(DesignInterface::class);
        $this->cache = $this->getMockForAbstractClass(FrontendInterface::class);
        $this->viewFileSystem = $this->createMock(FilesystemView::class);
        $this->moduleList = $this->createMock(ModuleList::class);
        $this->modulesReader = $this->createMock(Reader::class);
        $this->scopeResolver = $this->getMockForAbstractClass(ScopeResolverInterface::class);
        $this->resource = $this->getMockForAbstractClass(ResourceInterface::class);
        $this->locale = $this->getMockForAbstractClass(ResolverInterface::class);
        $this->appState = $this->createMock(State::class);
        $this->request = $this->getMockForAbstractClass(
            RequestInterface::class,
            [],
            '',
            false,
            false,
            true,
            ['getParam', 'getControllerModule']
        );
        $this->csvParser = $this->createMock(Csv::class);
        $this->packDictionary = $this->createMock(Dictionary::class);
        $this->directory = $this->getMockForAbstractClass(ReadInterface::class);
        $filesystem = $this->createMock(Filesystem::class);
        $filesystem->expects($this->once())->method('getDirectoryRead')->willReturn($this->directory);
        $this->fileDriver = $this->getMockForAbstractClass(DriverInterface::class);

        $this->translate = new Translate(
            $this->viewDesign,
            $this->cache,
            $this->viewFileSystem,
            $this->moduleList,
            $this->modulesReader,
            $this->scopeResolver,
            $this->resource,
            $this->locale,
            $this->appState,
            $filesystem,
            $this->request,
            $this->csvParser,
            $this->packDictionary,
            $this->fileDriver
        );

        $serializerMock = $this->getMockForAbstractClass(SerializerInterface::class);
        $serializerMock->method('serialize')
            ->willReturnCallback(function ($data) {
                return json_encode($data);
            });
        $serializerMock->method('unserialize')
            ->willReturnCallback(function ($string) {
                return json_decode($string, true);
            });
        $objectManager->setBackwardCompatibleProperty(
            $this->translate,
            'serializer',
            $serializerMock
        );
    }

    /**
     * @param string $area
     * @param bool $forceReload
     * @param array $cachedData
     *
     * @return void
     * @dataProvider dataProviderLoadDataCachedTranslation
     */
    public function testLoadDataCachedTranslation($area, $forceReload, $cachedData): void
    {
        $this->expectsSetConfig('Magento/luma');

        $this->cache->expects($this->once())
            ->method('load')
            ->willReturn(json_encode($cachedData));

        $this->appState->expects($this->exactly($area ? 0 : 1))
            ->method('getAreaCode')
            ->willReturn('frontend');

        $this->translate->loadData($area, $forceReload);
        $this->assertEquals($cachedData, $this->translate->getData());
    }

    /**
     * @return array
     */
    public static function dataProviderLoadDataCachedTranslation(): array
    {
        $cachedData = ['cached 1' => 'translated 1', 'cached 2' => 'translated 2'];
        return [
            ['adminhtml', false, $cachedData],
            ['frontend', false, $cachedData],
            [null, false, $cachedData]
        ];
    }

    /**
     * @param string $area
     * @param bool $forceReload
     *
     * @return void
     * @dataProvider dataProviderForTestLoadData
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function testLoadData($area, $forceReload): void
    {
        $this->expectsSetConfig('Magento/luma');

        $this->appState->expects($this->exactly($area ? 0 : 1))
            ->method('getAreaCode')
            ->willReturn('frontend');

        $this->cache->expects($this->exactly($forceReload ? 0 : 1))
            ->method('load')
            ->willReturn(false);

        $this->directory->expects($this->any())->method('isExist')->willReturn(true);

        // _loadModuleTranslation()
        $modules = ['some_module', 'other_module', 'another_module', 'current_module'];
        $this->request->expects($this->any())
            ->method('getControllerModule')
            ->willReturn('current_module');
        $this->moduleList->expects($this->once())->method('getNames')->willReturn($modules);
        $moduleData = [
            'module original' => 'module translated',
            'module theme' => 'module-theme original translated',
            'module pack' => 'module-pack original translated',
            'module db' => 'module-db original translated'
        ];
        $this->modulesReader->expects($this->any())->method('getModuleDir')->willReturn('/app/module');
        $themeData = [
            'theme original' => 'theme translated',
            'module theme' => 'theme translated overwrite',
            'module pack' => 'theme-pack translated overwrite',
            'module db' => 'theme-db translated overwrite'
        ];
        $this->csvParser->expects($this->any())
            ->method('getDataPairs')
            ->willReturnMap(
                [
                    ['/app/module/en_US.csv', 0, 1, $moduleData],
                    ['/app/module/en_GB.csv', 0, 1, $moduleData],
                    ['/theme.csv', 0, 1, $themeData]
                ]
            );
        $this->fileDriver->expects($this->any())
            ->method('isExists')
            ->willReturnMap(
                [
                    ['/app/module/en_US.csv', true],
                    ['/app/module/en_GB.csv', true],
                    ['/theme.csv', true]
                ]
            );

        // _loadPackTranslation
        $packData = [
            'pack original' => 'pack translated',
            'module pack' => 'pack translated overwrite',
            'module db' => 'pack-db translated overwrite',
        ];
        $this->packDictionary->expects($this->once())->method('getDictionary')->willReturn($packData);

        // _loadThemeTranslation()
        $this->viewFileSystem->expects($this->any())
            ->method('getLocaleFileName')
            ->willReturn('/theme.csv');

        // _loadDbTranslation()
        $dbData = [
            'db original' => 'db translated',
            'module db' => 'db translated overwrite',
        ];
        $this->resource->expects($this->any())->method('getTranslationArray')->willReturn($dbData);

        $this->cache->expects($this->exactly($forceReload ? 0 : 1))->method('save');

        $this->translate->loadData($area, $forceReload);

        $expected = [
            'module original' => 'module translated',
            'module theme' => 'theme translated overwrite',
            'module pack' => 'theme-pack translated overwrite',
            'module db' => 'db translated overwrite',
            'theme original' => 'theme translated',
            'pack original' => 'pack translated',
            'db original' => 'db translated'
        ];
        $this->assertEquals($expected, $this->translate->getData());
    }

    /**
     * @return array
     */
    public static function dataProviderForTestLoadData(): array
    {
        return [
            ['adminhtml', true],
            ['adminhtml', false],
            ['frontend', true],
            ['frontend', false],
            [null, true],
            [null, false]
        ];
    }

    /**
     * @param $data
     * @param $result
     *
     * @return void
     * @dataProvider dataProviderForTestGetData
     */
    public function testGetData($data, $result): void
    {
        $this->cache->expects($this->once())
            ->method('load')
            ->willReturn(json_encode($data));
        $this->expectsSetConfig('themeId');
        $this->translate->loadData('frontend');
        $this->assertEquals($result, $this->translate->getData());
    }

    /**
     * @return array
     */
    public static function dataProviderForTestGetData(): array
    {
        $data = ['original 1' => 'translated 1', 'original 2' => 'translated 2'];
        return [
            [$data, $data],
            [null, []]
        ];
    }

    /**
     * @return void
     */
    public function testGetLocale(): void
    {
        $this->locale->expects($this->once())->method('getLocale')->willReturn('en_US');
        $this->assertEquals('en_US', $this->translate->getLocale());

        $this->locale->expects($this->never())->method('getLocale');
        $this->assertEquals('en_US', $this->translate->getLocale());

        $this->locale->expects($this->never())->method('getLocale');
        $this->translate->setLocale('en_GB');
        $this->assertEquals('en_GB', $this->translate->getLocale());
    }

    /**
     * @return void
     */
    public function testSetLocale(): void
    {
        $this->translate->setLocale('en_GB');
        $this->locale->expects($this->never())->method('getLocale');
        $this->assertEquals('en_GB', $this->translate->getLocale());
    }

    /**
     * @return void
     */
    public function testGetTheme(): void
    {

        $requestTheme = ['theme_title' => 'Theme Title'];
        $this->request
            ->method('getParam')
            ->willReturnCallback(
                function ($arg1) use ($requestTheme) {
                    static $callCount = 0;
                    if ($callCount == 0 && $arg1 == 'theme') {
                        $callCount++;
                        return '';
                    } elseif ($callCount == 1 && $arg1 == 'theme') {
                        $callCount++;
                        return $requestTheme;
                    }
                }
            );

        $this->assertEquals('theme', $this->translate->getTheme());
        $this->assertEquals('themeTheme Title', $this->translate->getTheme());
    }

    /**
     * @return void
     */
    public function testLoadDataNoTheme(): void
    {
        $forceReload = true;
        $this->expectsSetConfig(null, null);
        $this->moduleList->expects($this->once())->method('getNames')->willReturn([]);
        $this->appState->expects($this->once())->method('getAreaCode')->willReturn('frontend');
        $this->packDictionary->expects($this->once())->method('getDictionary')->willReturn([]);
        $this->resource->expects($this->any())->method('getTranslationArray')->willReturn([]);
        $this->assertEquals($this->translate, $this->translate->loadData(null, $forceReload));
    }

    /**
     * Declare calls expectation for setConfig() method.
     *
     * @return void
     */
    protected function expectsSetConfig($themeId, $localeCode = 'en_US'): void
    {
        $this->locale->expects($this->any())->method('getLocale')->willReturn($localeCode);
        $scope = new DataObject(['code' => 'frontendCode', 'id' => 1]);
        $scopeAdmin = new DataObject(['code' => 'adminCode', 'id' => 0]);
        $this->scopeResolver->expects($this->any())
            ->method('getScope')
            ->willReturnMap(
                [
                    [null, $scope],
                    ['admin', $scopeAdmin]
                ]
            );
        $designTheme = $this->getMockBuilder(Theme::class)
            ->disableOriginalConstructor()
            ->getMock();

        $designTheme->expects($this->once())
            ->method('getThemePath')
            ->willReturn($themeId);

        $this->viewDesign->expects($this->any())->method('getDesignTheme')->willReturn($designTheme);
    }
}
