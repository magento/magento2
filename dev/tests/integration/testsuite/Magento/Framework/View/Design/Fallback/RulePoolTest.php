<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Design\Fallback;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Filesystem;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Factory Test
 */
class RulePoolTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Fake module name
     */
    const MODULE = 'Test_FrameworkViewFallback';

    /**#@+
     *  Fake theme paths
     */
    const THEME_ONE = 'frontend/FrameworkViewFallback/test-theme-one';
    const THEME_TWO = 'frontend/FrameworkViewFallback/test-theme-two';
    /**#@-*/

    /**
     * @var RulePool
     */
    protected $model;

    /**
     * @var array
     */
    protected $defaultParams;

    public static function setUpBeforeClass()
    {
        ComponentRegistrar::register(ComponentRegistrar::MODULE, self::MODULE, '/module/path');
        ComponentRegistrar::register(ComponentRegistrar::THEME, self::THEME_ONE, '/theme/one/path');
        ComponentRegistrar::register(ComponentRegistrar::THEME, self::THEME_TWO, '/theme/two/path');
    }

    protected function setUp()
    {
        $this->model = Bootstrap::getObjectManager()->create('Magento\Framework\View\Design\Fallback\RulePool');
        $parentTheme = $this->getMockForAbstractClass('Magento\Framework\View\Design\ThemeInterface');
        $parentTheme->expects($this->any())->method('getFullPath')->will($this->returnValue(self::THEME_TWO));
        $theme = $this->getMockForAbstractClass('Magento\Framework\View\Design\ThemeInterface');
        $theme->expects($this->any())->method('getFullPath')->will($this->returnValue(self::THEME_ONE));
        $theme->expects($this->any())->method('getParentTheme')->will($this->returnValue($parentTheme));
        $this->defaultParams = [
            'area' => 'area',
            'theme' => $theme,
            'module_name' => self::MODULE,
            'locale' => 'en_US',
        ];
    }

    protected function tearDown()
    {
        $this->model = null;
        $this->defaultParams = [];
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedException Fallback rule 'unsupported_type' is not supported
     */
    public function testGetRuleUnsupportedType()
    {
        $this->model->getRule('unsupported_type');
    }

    /**
     * @param string $type
     * @param array $overriddenParams
     * @param string $expectedErrorMessage
     *
     * @dataProvider getPatternDirsExceptionDataProvider
     */
    public function testGetPatternDirsException($type, array $overriddenParams, $expectedErrorMessage)
    {
        $this->setExpectedException('InvalidArgumentException', $expectedErrorMessage);
        $params = $overriddenParams + $this->defaultParams;
        $this->model->getRule($type)->getPatternDirs($params);
    }

    /**
     * @return array
     */
    public function getPatternDirsExceptionDataProvider()
    {
        $exceptions = [
            'no theme' => [
                ['theme' => null],
                'Parameter "theme" should be specified and should implement the theme interface',
            ],
            'no area' => [
                ['area' => null],
                "Required parameter 'area' was not passed",
            ],
        ];
        $exceptionsPerTypes = [
            \Magento\Framework\View\Design\Fallback\RulePool::TYPE_LOCALE_FILE => [
                'no theme',
            ],
            \Magento\Framework\View\Design\Fallback\RulePool::TYPE_FILE => [
                'no theme', 'no area',
            ],
            \Magento\Framework\View\Design\Fallback\RulePool::TYPE_TEMPLATE_FILE => [
                'no theme', 'no area',
            ],
            \Magento\Framework\View\Design\Fallback\RulePool::TYPE_STATIC_FILE => [
                'no theme', 'no area',
            ],
        ];

        $data = [];
        foreach ($exceptionsPerTypes as $type => $exceptionKeys) {
            foreach ($exceptionKeys as $key) {
                $data[$type . ', ' . $key] = [$type, $exceptions[$key][0], $exceptions[$key][1]];
            }
        }

        return $data;
    }

    /**
     * @param string $type
     * @param array $overriddenParams
     * @param array $expectedResult
     *
     * @dataProvider getPatternDirsDataProvider
     */
    public function testGetPatternDirs($type, array $overriddenParams, array $expectedResult)
    {
        $actualResult = $this->model->getRule($type)
            ->getPatternDirs($overriddenParams + $this->defaultParams);
        $this->assertEquals($expectedResult, $actualResult);
    }

    /**
     * @return array
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function getPatternDirsDataProvider()
    {
        $objectManager = Bootstrap::getObjectManager();
        /** @var \Magento\Framework\Component\ComponentRegistrarInterface $componentRegistrar */
        $componentRegistrar = $objectManager->get(
            '\Magento\Framework\Component\ComponentRegistrarInterface'
        );
        $coreModulePath = $componentRegistrar->getPath(ComponentRegistrar::MODULE, 'Magento_Theme');
        /** @var \Magento\Framework\Filesystem $filesystem */
        $filesystem = $objectManager->get('\Magento\Framework\Filesystem');
        $libPath = rtrim($filesystem->getDirectoryRead(DirectoryList::LIB_WEB)->getAbsolutePath(), '/');

        return [
            'locale' => [
                \Magento\Framework\View\Design\Fallback\RulePool::TYPE_LOCALE_FILE,
                [],
                [
                    '/theme/one/path',
                    '/theme/two/path',
                ],
            ],
            'file, modular' => [
                \Magento\Framework\View\Design\Fallback\RulePool::TYPE_FILE,
                [],
                [
                    '/theme/one/path/Test_FrameworkViewFallback',
                    '/theme/two/path/Test_FrameworkViewFallback',
                    '/module/path/view/area',
                    '/module/path/view/base',
                ],
            ],
            'file, non-modular' => [
                \Magento\Framework\View\Design\Fallback\RulePool::TYPE_FILE,
                ['namespace' => null, 'module_name' => null],
                [
                    '/theme/one/path',
                    '/theme/two/path',
                ],
            ],

            'template, modular' => [
                \Magento\Framework\View\Design\Fallback\RulePool::TYPE_TEMPLATE_FILE,
                [],
                [
                    '/theme/one/path/Test_FrameworkViewFallback/templates',
                    '/theme/two/path/Test_FrameworkViewFallback/templates',
                    '/module/path/view/area/templates',
                    '/module/path/view/base/templates',
                ],
            ],
            'template, non-modular' => [
                \Magento\Framework\View\Design\Fallback\RulePool::TYPE_TEMPLATE_FILE,
                ['namespace' => null, 'module_name' => null],
                [
                    '/theme/one/path/templates',
                    '/theme/two/path/templates',
                ],
            ],
            'template, non-modular-magento-core' => [
                \Magento\Framework\View\Design\Fallback\RulePool::TYPE_TEMPLATE_FILE,
                ['module_name' => 'Magento_Theme'],
                [
                    '/theme/one/path/Magento_Theme/templates',
                    '/theme/two/path/Magento_Theme/templates',
                    $coreModulePath . '/view/area/templates',
                    $coreModulePath . '/view/base/templates',
                ],
            ],

            'view, modular localized' => [
                \Magento\Framework\View\Design\Fallback\RulePool::TYPE_STATIC_FILE,
                [],
                [
                    '/theme/one/path/Test_FrameworkViewFallback/web/i18n/en_US',
                    '/theme/one/path/Test_FrameworkViewFallback/web',
                    '/theme/two/path/Test_FrameworkViewFallback/web/i18n/en_US',
                    '/theme/two/path/Test_FrameworkViewFallback/web',
                    '/module/path/view/area/web/i18n/en_US',
                    '/module/path/view/base/web/i18n/en_US',
                    '/module/path/view/area/web',
                    '/module/path/view/base/web',
                ],
            ],
            'view, modular non-localized' => [
                \Magento\Framework\View\Design\Fallback\RulePool::TYPE_STATIC_FILE,
                ['locale' => null],
                [
                    '/theme/one/path/Test_FrameworkViewFallback/web',
                    '/theme/two/path/Test_FrameworkViewFallback/web',
                    '/module/path/view/area/web',
                    '/module/path/view/base/web',
                ],
            ],
            'view, non-modular localized' => [
                \Magento\Framework\View\Design\Fallback\RulePool::TYPE_STATIC_FILE,
                ['module_name' => null],
                [
                    '/theme/one/path/web/i18n/en_US',
                    '/theme/one/path/web',
                    '/theme/two/path/web/i18n/en_US',
                    '/theme/two/path/web',
                    $libPath,
                ],
            ],
            'view, non-modular non-localized' => [
                \Magento\Framework\View\Design\Fallback\RulePool::TYPE_STATIC_FILE,
                ['module_name' => null, 'locale' => null],
                [
                    '/theme/one/path/web',
                    '/theme/two/path/web',
                    $libPath,
                ],
            ],
            // Single test, as emails will always be loaded in a modular context with no locale specificity
            'email' => [
                \Magento\Framework\View\Design\Fallback\RulePool::TYPE_EMAIL_TEMPLATE,
                [],
                [
                    '/theme/one/path/Test_FrameworkViewFallback/email',
                    '/theme/two/path/Test_FrameworkViewFallback/email',
                    '/module/path/view/area/email',
                ],
            ],
        ];
    }
}
