<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Test\Unit;

use \Magento\Framework\Translate;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class TranslateTest extends \PHPUnit_Framework_TestCase
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

    protected function setUp()
    {
        $this->viewDesign = $this->getMock(\Magento\Framework\View\DesignInterface::class, [], [], '', false);
        $this->cache = $this->getMock(\Magento\Framework\Cache\FrontendInterface::class, [], [], '', false);
        $this->viewFileSystem = $this->getMock(\Magento\Framework\View\FileSystem::class, [], [], '', false);
        $this->moduleList = $this->getMock(\Magento\Framework\Module\ModuleList::class, [], [], '', false);
        $this->modulesReader = $this->getMock(\Magento\Framework\Module\Dir\Reader::class, [], [], '', false);
        $this->scopeResolver = $this->getMock(\Magento\Framework\App\ScopeResolverInterface::class, [], [], '', false);
        $this->resource = $this->getMock(\Magento\Framework\Translate\ResourceInterface::class, [], [], '', false);
        $this->locale = $this->getMock(\Magento\Framework\Locale\ResolverInterface::class, [], [], '', false);
        $this->appState = $this->getMock(\Magento\Framework\App\State::class, [], [], '', false);
        $this->request = $this->getMockForAbstractClass(
            \Magento\Framework\App\RequestInterface::class,
            [],
            '',
            false,
            false,
            true,
            ['getParam', 'getControllerModule']
        );
        $this->csvParser = $this->getMock(\Magento\Framework\File\Csv::class, [], [], '', false);
        $this->packDictionary = $this->getMock(\Magento\Framework\App\Language\Dictionary::class, [], [], '', false);
        $this->directory = $this->getMock(
            \Magento\Framework\Filesystem\Directory\ReadInterface::class,
            [],
            [],
            '',
            false
        );
        $filesystem = $this->getMock(\Magento\Framework\Filesystem::class, [], [], '', false);
        $filesystem->expects($this->once())->method('getDirectoryRead')->will($this->returnValue($this->directory));

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
            $this->packDictionary
        );
    }

    /**
     * @param string $area
     * @param bool $forceReload
     * @param array $cachedData
     * @dataProvider dataProviderForTestLoadData
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function testLoadData($area, $forceReload, $cachedData)
    {
        $this->expectsSetConfig('themeId');

        $this->cache->expects($this->exactly($forceReload ? 0 : 1))
            ->method('load')
            ->will($this->returnValue(serialize($cachedData)));

        if (!$forceReload && $cachedData !== false) {
            $this->translate->loadData($area, $forceReload);
            $this->assertEquals($cachedData, $this->translate->getData());
            return;
        }

        $this->directory->expects($this->any())->method('isExist')->will($this->returnValue(true));

        // _loadModuleTranslation()
        $this->moduleList->expects($this->once())->method('getNames')->will($this->returnValue(['name']));
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

        // _loadThemeTranslation()
        $this->viewFileSystem->expects($this->any())
            ->method('getLocaleFileName')
            ->will($this->returnValue('/theme.csv'));

        // _loadPackTranslation
        $packData = [
            'pack original' => 'pack translated',
            'module pack' => 'pack translated overwrite',
            'module db' => 'pack-db translated overwrite',
        ];
        $this->packDictionary->expects($this->once())->method('getDictionary')->will($this->returnValue($packData));

        // _loadDbTranslation()
        $dbData = [
            'db original' => 'db translated',
            'module db' => 'db translated overwrite',
        ];
        $this->resource->expects($this->any())->method('getTranslationArray')->will($this->returnValue($dbData));

        if (!$forceReload) {
            $this->cache->expects($this->exactly(1))->method('save');
        }

        $this->translate->loadData($area, $forceReload);

        $expected = [
            'module original' => 'module translated',
            'module theme' => 'theme translated overwrite',
            'module pack' => 'pack translated overwrite',
            'module db' => 'db translated overwrite',
            'theme original' => 'theme translated',
            'pack original' => 'pack translated',
            'db original' => 'db translated',
        ];
        $this->assertEquals($expected, $this->translate->getData());
    }

    public function dataProviderForTestLoadData()
    {
        $cachedData = ['cached 1' => 'translated 1', 'cached 2' => 'translated 2'];
        return [
            ['adminhtml', true, false],
            ['adminhtml', true, $cachedData],
            ['adminhtml', false, $cachedData],
            ['adminhtml', false, false],
            ['frontend', true, false],
            ['frontend', true, $cachedData],
            ['frontend', false, $cachedData],
            ['frontend', false, false],
            [null, true, false],
            [null, true, $cachedData],
            [null, false, $cachedData],
            [null, false, false]
        ];
    }

    /**
     * @param $data
     * @param $result
     * @dataProvider dataProviderForTestGetData
     */
    public function testGetData($data, $result)
    {
        $this->cache->expects($this->once())
            ->method('load')
            ->will($this->returnValue(serialize($data)));
        $this->expectsSetConfig('themeId');
        $this->translate->loadData('frontend');
        $this->assertEquals($result, $this->translate->getData());
    }

    public function dataProviderForTestGetData()
    {
        $data = ['original 1' => 'translated 1', 'original 2' => 'translated 2'];
        return [
            [$data, $data],
            [null, []]
        ];
    }

    public function testGetLocale()
    {
        $this->locale->expects($this->once())->method('getLocale')->will($this->returnValue('en_US'));
        $this->assertEquals('en_US', $this->translate->getLocale());

        $this->locale->expects($this->never())->method('getLocale');
        $this->assertEquals('en_US', $this->translate->getLocale());

        $this->locale->expects($this->never())->method('getLocale');
        $this->translate->setLocale('en_GB');
        $this->assertEquals('en_GB', $this->translate->getLocale());
    }

    public function testSetLocale()
    {
        $this->translate->setLocale('en_GB');
        $this->locale->expects($this->never())->method('getLocale');
        $this->assertEquals('en_GB', $this->translate->getLocale());
    }

    public function testGetTheme()
    {
        $this->request->expects($this->at(0))->method('getParam')->with('theme')->will($this->returnValue(''));

        $requestTheme = ['theme_title' => 'Theme Title'];
        $this->request->expects($this->at(1))->method('getParam')->with('theme')
            ->will($this->returnValue($requestTheme));

        $this->assertEquals('theme', $this->translate->getTheme());
        $this->assertEquals('themeTheme Title', $this->translate->getTheme());
    }

    public function testLoadDataNoTheme()
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
    protected function expectsSetConfig($themeId, $localeCode = 'en_US')
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
        $designTheme = new \Magento\Framework\DataObject(['id' => $themeId]);
        $this->viewDesign->expects($this->any())->method('getDesignTheme')->will($this->returnValue($designTheme));
    }
}
