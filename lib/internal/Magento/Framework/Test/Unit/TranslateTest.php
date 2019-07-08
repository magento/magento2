<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Test\Unit;

use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\Translate;

/**
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class TranslateTest extends \PHPUnit\Framework\TestCase
{
    /** @var Translate */
    protected $translate;

    /** @var \Magento\Framework\View\DesignInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $viewDesign;

    /** @var \Magento\Framework\Cache\FrontendInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $cache;

    /** @var \Magento\Framework\View\FileSystem|\PHPUnit_Framework_MockObject_MockObject */
    protected $viewFileSystem;

    /** @var \Magento\Framework\Module\ModuleList|\PHPUnit_Framework_MockObject_MockObject */
    protected $moduleList;

    /** @var \Magento\Framework\Module\Dir\Reader|\PHPUnit_Framework_MockObject_MockObject */
    protected $modulesReader;

    /** @var \Magento\Framework\App\ScopeResolverInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $scopeResolver;

    /** @var \Magento\Framework\Translate\ResourceInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $resource;

    /** @var \Magento\Framework\Locale\ResolverInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $locale;

    /** @var \Magento\Framework\App\State|\PHPUnit_Framework_MockObject_MockObject */
    protected $appState;

    /** @var \Magento\Framework\Filesystem|\PHPUnit_Framework_MockObject_MockObject */
    protected $filesystem;

    /** @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $request;

    /** @var \Magento\Framework\File\Csv|\PHPUnit_Framework_MockObject_MockObject */
    protected $csvParser;

    /** @var  \Magento\Framework\App\Language\Dictionary|\PHPUnit_Framework_MockObject_MockObject */
    protected $packDictionary;

    /** @var \Magento\Framework\Filesystem\Directory\ReadInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $directory;

    /** @var \Magento\Framework\Filesystem\DriverInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $fileDriver;

    protected function setUp(): void
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->viewDesign = $this->createMock(\Magento\Framework\View\DesignInterface::class);
        $this->cache = $this->createMock(\Magento\Framework\Cache\FrontendInterface::class);
        $this->viewFileSystem = $this->createMock(\Magento\Framework\View\FileSystem::class);
        $this->moduleList = $this->createMock(\Magento\Framework\Module\ModuleList::class);
        $this->modulesReader = $this->createMock(\Magento\Framework\Module\Dir\Reader::class);
        $this->scopeResolver = $this->createMock(\Magento\Framework\App\ScopeResolverInterface::class);
        $this->resource = $this->createMock(\Magento\Framework\Translate\ResourceInterface::class);
        $this->locale = $this->createMock(\Magento\Framework\Locale\ResolverInterface::class);
        $this->appState = $this->createMock(\Magento\Framework\App\State::class);
        $this->request = $this->getMockForAbstractClass(
            \Magento\Framework\App\RequestInterface::class,
            [],
            '',
            false,
            false,
            true,
            ['getParam', 'getControllerModule']
        );
        $this->csvParser = $this->createMock(\Magento\Framework\File\Csv::class);
        $this->packDictionary = $this->createMock(\Magento\Framework\App\Language\Dictionary::class);
        $this->directory = $this->createMock(\Magento\Framework\Filesystem\Directory\ReadInterface::class);
        $filesystem = $this->createMock(\Magento\Framework\Filesystem::class);
        $filesystem->expects($this->once())->method('getDirectoryRead')->will($this->returnValue($this->directory));
        $this->fileDriver = $this->createMock(\Magento\Framework\Filesystem\DriverInterface::class);

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

        $serializerMock = $this->createMock(SerializerInterface::class);
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
     * @dataProvider dataProviderLoadDataCachedTranslation
     */
    public function testLoadDataCachedTranslation($area, $forceReload, $cachedData): void
    {
        $this->expectsSetConfig('Magento/luma');

        $this->cache->expects($this->once())
            ->method('load')
            ->will($this->returnValue(json_encode($cachedData)));

        $this->appState->expects($this->exactly($area ? 0 : 1))
            ->method('getAreaCode')
            ->will($this->returnValue('frontend'));

        $this->translate->loadData($area, $forceReload);
        $this->assertEquals($cachedData, $this->translate->getData());
    }

    /**
     * @return array
     */
    public function dataProviderLoadDataCachedTranslation(): array
    {
        $cachedData = ['cached 1' => 'translated 1', 'cached 2' => 'translated 2'];
        return [
            ['adminhtml', false, $cachedData],
            ['frontend', false, $cachedData],
            [null, false, $cachedData],
        ];
    }

    /**
     * @param string $area
     * @param bool $forceReload
     * @dataProvider dataProviderForTestLoadData
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function testLoadData($area, $forceReload): void
    {
        $this->expectsSetConfig('Magento/luma');

        $this->appState->expects($this->exactly($area ? 0 : 1))
            ->method('getAreaCode')
            ->will($this->returnValue('frontend'));

        $this->cache->expects($this->exactly($forceReload ? 0 : 1))
            ->method('load')
            ->will($this->returnValue(false));

        $this->directory->expects($this->any())->method('isExist')->will($this->returnValue(true));

        // _loadModuleTranslation()
        $modules = ['some_module', 'other_module', 'another_module', 'current_module'];
        $this->request->expects($this->any())
            ->method('getControllerModule')
            ->willReturn('current_module');
        $this->moduleList->expects($this->once())->method('getNames')->will($this->returnValue($modules));
        $moduleData = [
            'module original' => 'module translated',
            'module theme' => 'module-theme original translated',
            'module pack' => 'module-pack original translated',
            'module db' => 'module-db original translated',
        ];
        $this->modulesReader->expects($this->any())->method('getModuleDir')->will($this->returnValue('/app/module'));
        $themeData = [
            'theme original' => 'theme translated',
            'module theme' => 'theme translated overwrite',
            'module pack' => 'theme-pack translated overwrite',
            'module db' => 'theme-db translated overwrite',
        ];
        $this->csvParser->expects($this->any())
            ->method('getDataPairs')
            ->will(
                $this->returnValueMap(
                    [
                        ['/app/module/en_US.csv', 0, 1, $moduleData],
                        ['/app/module/en_GB.csv', 0, 1, $moduleData],
                        ['/theme.csv', 0, 1, $themeData],
                    ]
                )
            );
        $this->fileDriver->expects($this->any())
            ->method('isExists')
            ->will(
                $this->returnValueMap(
                    [
                        ['/app/module/en_US.csv', true],
                        ['/app/module/en_GB.csv', true],
                        ['/theme.csv', true],
                    ]
                )
            );

        // _loadPackTranslation
        $packData = [
            'pack original' => 'pack translated',
            'module pack' => 'pack translated overwrite',
            'module db' => 'pack-db translated overwrite',
        ];
        $this->packDictionary->expects($this->once())->method('getDictionary')->will($this->returnValue($packData));

        // _loadThemeTranslation()
        $this->viewFileSystem->expects($this->any())
            ->method('getLocaleFileName')
            ->will($this->returnValue('/theme.csv'));

        // _loadDbTranslation()
        $dbData = [
            'db original' => 'db translated',
            'module db' => 'db translated overwrite',
        ];
        $this->resource->expects($this->any())->method('getTranslationArray')->will($this->returnValue($dbData));

        $this->cache->expects($this->exactly($forceReload ? 0 : 1))->method('save');

        $this->translate->loadData($area, $forceReload);

        $expected = [
            'module original' => 'module translated',
            'module theme' => 'theme translated overwrite',
            'module pack' => 'theme-pack translated overwrite',
            'module db' => 'db translated overwrite',
            'theme original' => 'theme translated',
            'pack original' => 'pack translated',
            'db original' => 'db translated',
        ];
        $this->assertEquals($expected, $this->translate->getData());
    }

    /**
     * @return array
     */
    public function dataProviderForTestLoadData(): array
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
     * @dataProvider dataProviderForTestGetData
     */
    public function testGetData($data, $result): void
    {
        $this->cache->expects($this->once())
            ->method('load')
            ->will($this->returnValue(json_encode($data)));
        $this->expectsSetConfig('themeId');
        $this->translate->loadData('frontend');
        $this->assertEquals($result, $this->translate->getData());
    }

    /**
     * @return array
     */
    public function dataProviderForTestGetData(): array
    {
        $data = ['original 1' => 'translated 1', 'original 2' => 'translated 2'];
        return [
            [$data, $data],
            [null, []]
        ];
    }

    public function testGetLocale(): void
    {
        $this->locale->expects($this->once())->method('getLocale')->will($this->returnValue('en_US'));
        $this->assertEquals('en_US', $this->translate->getLocale());

        $this->locale->expects($this->never())->method('getLocale');
        $this->assertEquals('en_US', $this->translate->getLocale());

        $this->locale->expects($this->never())->method('getLocale');
        $this->translate->setLocale('en_GB');
        $this->assertEquals('en_GB', $this->translate->getLocale());
    }

    public function testSetLocale(): void
    {
        $this->translate->setLocale('en_GB');
        $this->locale->expects($this->never())->method('getLocale');
        $this->assertEquals('en_GB', $this->translate->getLocale());
    }

    public function testGetTheme(): void
    {
        $this->request->expects($this->at(0))->method('getParam')->with('theme')->will($this->returnValue(''));

        $requestTheme = ['theme_title' => 'Theme Title'];
        $this->request->expects($this->at(1))->method('getParam')->with('theme')
            ->will($this->returnValue($requestTheme));

        $this->assertEquals('theme', $this->translate->getTheme());
        $this->assertEquals('themeTheme Title', $this->translate->getTheme());
    }

    public function testLoadDataNoTheme(): void
    {
        $forceReload = true;
        $this->expectsSetConfig(null, null);
        $this->moduleList->expects($this->once())->method('getNames')->will($this->returnValue([]));
        $this->appState->expects($this->once())->method('getAreaCode')->will($this->returnValue('frontend'));
        $this->packDictionary->expects($this->once())->method('getDictionary')->will($this->returnValue([]));
        $this->resource->expects($this->any())->method('getTranslationArray')->will($this->returnValue([]));
        $this->assertEquals($this->translate, $this->translate->loadData(null, $forceReload));
    }

    /**
     * Declare calls expectation for setConfig() method
     */
    protected function expectsSetConfig($themeId, $localeCode = 'en_US'): void
    {
        $this->locale->expects($this->any())->method('getLocale')->will($this->returnValue($localeCode));
        $scope = new \Magento\Framework\DataObject(['code' => 'frontendCode', 'id' => 1]);
        $scopeAdmin = new \Magento\Framework\DataObject(['code' => 'adminCode', 'id' => 0]);
        $this->scopeResolver->expects($this->any())
            ->method('getScope')
            ->will(
                $this->returnValueMap(
                    [
                        [null, $scope],
                        ['admin', $scopeAdmin],
                    ]
                )
            );
        $designTheme = $this->getMockBuilder(\Magento\Theme\Model\Theme::class)
            ->disableOriginalConstructor()
            ->getMock();

        $designTheme->expects($this->once())
            ->method('getThemePath')
            ->willReturn($themeId);

        $this->viewDesign->expects($this->any())->method('getDesignTheme')->will($this->returnValue($designTheme));
    }
}
