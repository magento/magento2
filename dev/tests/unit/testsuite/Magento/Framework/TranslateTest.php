<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Framework;

use Magento\TestFramework\Matcher\MethodInvokedAtIndex;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class TranslateTest extends \PHPUnit_Framework_TestCase
{
    /** @var Translate */
    protected $_translate;

    /** @var \Magento\Framework\View\DesignInterface */
    protected $_viewDesign;

    /** @var \Magento\Framework\Cache\FrontendInterface */
    protected $_cache;

    /** @var \Magento\Framework\View\FileSystem */
    protected $_viewFileSystem;

    /** @var \Magento\Framework\Module\ModuleList */
    protected $_moduleList;

    /** @var \Magento\Framework\Module\Dir\Reader */
    protected $_modulesReader;

    /** @var \Magento\Framework\App\ScopeResolverInterface */
    protected $_scopeResolver;

    /** @var \Magento\Framework\Translate\ResourceInterface */
    protected $_resource;

    /** @var \Magento\Framework\Locale\ResolverInterface */
    protected $_locale;

    /** @var \Magento\Framework\App\State */
    protected $_appState;

    /** @var \Magento\Framework\App\Filesystem */
    protected $_filesystem;

    /** @var \Magento\Framework\App\RequestInterface */
    protected $_request;

    /** @var \Magento\Framework\File\Csv */
    protected $_csvParser;

    /** @var  \Magento\Framework\App\Language\Dictionary|\PHPUnit_Framework_MockObject_MockObject */
    protected $_packDictionary;

    /** @var \Magento\Framework\Filesystem\Directory\ReadInterface */
    protected $_directory;

    public function setUp()
    {
        $this->_viewDesign = $this->getMock('\Magento\Framework\View\DesignInterface', [], [], '', false);
        $this->_cache = $this->getMock('\Magento\Framework\Cache\FrontendInterface', [], [], '', false);
        $this->_viewFileSystem = $this->getMock('\Magento\Framework\View\FileSystem', [], [], '', false);
        $this->_moduleList = $this->getMock('\Magento\Framework\Module\ModuleList', [], [], '', false);
        $this->_modulesReader = $this->getMock('\Magento\Framework\Module\Dir\Reader', [], [], '', false);
        $this->_scopeResolver = $this->getMock('\Magento\Framework\App\ScopeResolverInterface', [], [], '', false);
        $this->_resource = $this->getMock('\Magento\Framework\Translate\ResourceInterface', [], [], '', false);
        $this->_locale = $this->getMock('\Magento\Framework\Locale\ResolverInterface', [], [], '', false);
        $this->_appState = $this->getMock('\Magento\Framework\App\State', [], [], '', false);
        $this->_request = $this->getMock('\Magento\Framework\App\RequestInterface', [], [], '', false);
        $this->_csvParser = $this->getMock('\Magento\Framework\File\Csv', [], [], '', false);
        $this->_packDictionary = $this->getMock('\Magento\Framework\App\Language\Dictionary', [], [], '', false);
        $this->_directory = $this->getMock('\Magento\Framework\Filesystem\Directory\ReadInterface', [], [], '', false);
        $filesystem = $this->getMock('\Magento\Framework\App\Filesystem', [], [], '', false);
        $filesystem->expects($this->once())->method('getDirectoryRead')->will($this->returnValue($this->_directory));

        $this->_translate = new Translate(
            $this->_viewDesign,
            $this->_cache,
            $this->_viewFileSystem,
            $this->_moduleList,
            $this->_modulesReader,
            $this->_scopeResolver,
            $this->_resource,
            $this->_locale,
            $this->_appState,
            $filesystem,
            $this->_request,
            $this->_csvParser,
            $this->_packDictionary
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
        $this->_expectsSetConfig('themeId');

        $this->_cache->expects($this->exactly($forceReload ? 0 : 1))
            ->method('load')
            ->will($this->returnValue(serialize($cachedData)));

        if (!$forceReload && $cachedData !== false) {
            $this->_translate->loadData($area, $forceReload);
            $this->assertEquals($cachedData, $this->_translate->getData());
            return;
        }

        $this->_directory->expects($this->any())->method('isExist')->will($this->returnValue(true));

        // _loadModuleTranslation()
        $modules = [['name' => 'module']];
        $this->_moduleList->expects($this->once())->method('getModules')->will($this->returnValue($modules));
        $moduleData = [
            'module original' => 'module translated',
            'module original2' => 'module original2'
        ];
        $this->_modulesReader->expects($this->any())->method('getModuleDir')->will($this->returnValue('/app/module'));
        $themeData = ['theme original' => 'theme translated'];
        $this->_csvParser->expects($this->any())
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
        $this->_viewFileSystem->expects($this->any())
            ->method('getLocaleFileName')
            ->will($this->returnValue('/theme.csv'));

        // _loadPackTranslation
        $packData = ['pack original' => 'pack translated'];
        $this->_packDictionary->expects($this->once())->method('getDictionary')->will($this->returnValue($packData));

        // _loadDbTranslation()
        $dbData = ['db original' => 'db translated'];
        $this->_resource->expects($this->any())->method('getTranslationArray')->will($this->returnValue($dbData));

        $this->_cache->expects($this->exactly($forceReload ? 0 : 1))
            ->method('save');

        $this->_translate->loadData($area, $forceReload);

        $expected = [
            'module original' => 'module translated',
            'theme original' => 'theme translated',
            'pack original' => 'pack translated',
            'db original' => 'db translated'
        ];
        $this->assertEquals($expected, $this->_translate->getData());
    }

    public function dataProviderForTestLoadData()
    {
        $cachedData = ['cached 1' => 'translated 1', 'cached 2' => 'translated 2'];
        return [
            ['adminhtml', true, false],
            ['adminhtml', false, $cachedData],
            ['frontend', true, false],
            ['frontend', false, $cachedData],
            [null, true, false],
            [null, false, $cachedData],
            ['adminhtml', false, false]
        ];
    }

    /**
     * @param $data
     * @param $result
     * @dataProvider dataProviderForTestGetData
     */
    public function testGetData($data, $result)
    {
        $this->_cache->expects($this->once())
            ->method('load')
            ->will($this->returnValue(serialize($data)));
        $this->_expectsSetConfig('themeId');
        $this->_translate->loadData('frontend');
        $this->assertEquals($result, $this->_translate->getData());
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
        $this->_locale->expects($this->once())->method('getLocaleCode')->will($this->returnValue('en_US'));
        $this->assertEquals('en_US', $this->_translate->getLocale());

        $this->_locale->expects($this->never())->method('getLocaleCode');
        $this->assertEquals('en_US', $this->_translate->getLocale());

        $this->_locale->expects($this->never())->method('getLocaleCode');
        $this->_translate->setLocale('en_GB');
        $this->assertEquals('en_GB', $this->_translate->getLocale());
    }

    public function testSetLocale()
    {
        $this->_translate->setLocale('en_GB');
        $this->_locale->expects($this->never())->method('getLocaleCode');
        $this->assertEquals('en_GB', $this->_translate->getLocale());
    }

    public function testGetTheme()
    {
        $this->_request->expects($this->at(0))->method('getParam')->with('theme')->will($this->returnValue(''));

        $requestTheme = array('theme_title' => 'Theme Title');
        $this->_request->expects($this->at(1))->method('getParam')->with('theme')
            ->will($this->returnValue($requestTheme));

        $this->assertEquals('theme', $this->_translate->getTheme());
        $this->assertEquals('themeTheme Title', $this->_translate->getTheme());
    }

    public function testLoadDataNoTheme()
    {
        $forceReload = true;
        $this->_expectsSetConfig(null, null);
        $this->_moduleList->expects($this->once())->method('getModules')->will($this->returnValue([]));
        $this->_appState->expects($this->once())->method('getAreaCode')->will($this->returnValue('frontend'));
        $this->_packDictionary->expects($this->once())->method('getDictionary')->will($this->returnValue([]));
        $this->_resource->expects($this->any())->method('getTranslationArray')->will($this->returnValue([]));
        $this->assertEquals($this->_translate, $this->_translate->loadData(null, $forceReload));
    }

    /**
     * Declare calls expectation for setConfig() method
     */
    protected function _expectsSetConfig($themeId, $localeCode = 'en_US')
    {
        $this->_locale->expects($this->any())->method('getLocaleCode')->will($this->returnValue($localeCode));
        $scope = new \Magento\Framework\Object();
        $scopeAdmin = new \Magento\Framework\Object(['code' => 'adminCode', 'id' => 1]);
        $this->_scopeResolver->expects($this->any())
            ->method('getScope')
            ->will(
                $this->returnValueMap(
                    [
                        [null, $scope],
                        ['admin', $scopeAdmin]
                    ]
                )
            );
        $designTheme = new \Magento\Framework\Object(['id' => $themeId]);
        $this->_viewDesign->expects($this->any())->method('getDesignTheme')->will($this->returnValue($designTheme));
    }
}
