<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Design\Fallback;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;

/**
 * Factory Test
 */
class RulePoolTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var RulePool
     */
    protected $model;

    /**
     * @var array
     */
    protected $defaultParams;

    protected function setUp()
    {
        $filesystemMock = $this->getMock('Magento\Framework\Filesystem', [], [], '', false);
        $filesystemMock->expects($this->any())
            ->method('getDirectoryRead')
            ->will($this->returnCallback(function ($code) {
                $dirMock = $this->getMockForAbstractClass('Magento\Framework\Filesystem\Directory\ReadInterface');
                $dirMock->expects($this->any())
                    ->method('getAbsolutePath')
                    ->will($this->returnCallback(function ($path) use ($code) {
                        $path = empty($path) ? $path : '/' . $path;
                        return rtrim($code, '/') . $path;
                    }));
                return $dirMock;
            }));

        $this->model = new RulePool($filesystemMock);

        $parentTheme = $this->getMockForAbstractClass('Magento\Framework\View\Design\ThemeInterface');
        $parentTheme->expects($this->any())->method('getThemePath')->will($this->returnValue('parent_theme_path'));

        $theme = $this->getMockForAbstractClass('Magento\Framework\View\Design\ThemeInterface');
        $theme->expects($this->any())->method('getThemePath')->will($this->returnValue('current_theme_path'));
        $theme->expects($this->any())->method('getParentTheme')->will($this->returnValue($parentTheme));

        $this->defaultParams = [
            'area' => 'area',
            'theme' => $theme,
            'namespace' => 'namespace',
            'module' => 'module',
            'locale' => 'en_US',
        ];
    }

    protected function tearDown()
    {
        $this->model = null;
        $this->defaultParams = [];
    }

    /**
     * @param string $type
     *
     * @dataProvider getRuleDataProvider
     */
    public function testGetRule($type)
    {
        $actualResult = $this->model->getRule($type);
        $this->assertInstanceOf('\Magento\Framework\View\Design\Fallback\Rule\RuleInterface', $actualResult);
        $this->assertSame($actualResult, $this->model->getRule($type));
    }

    /**
     * @return array
     */
    public function getRuleDataProvider()
    {
        return [
            [\Magento\Framework\View\Design\Fallback\RulePool::TYPE_LOCALE_FILE],
            [\Magento\Framework\View\Design\Fallback\RulePool::TYPE_FILE],
            [\Magento\Framework\View\Design\Fallback\RulePool::TYPE_TEMPLATE_FILE],
            [\Magento\Framework\View\Design\Fallback\RulePool::TYPE_STATIC_FILE],
        ];
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
        $this->model->getRule($type)->getPatternDirs($overriddenParams + $this->defaultParams);
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
            'no namespace' => [
                ['namespace' => null],
                "Parameters 'namespace' and 'module' should either be both set or unset",
            ],
            'no module' => [
                ['module' => null],
                "Parameters 'namespace' and 'module' should either be both set or unset",
            ],
        ];
        $exceptionsPerTypes = [
            \Magento\Framework\View\Design\Fallback\RulePool::TYPE_LOCALE_FILE => [
                'no theme', 'no area',
            ],
            \Magento\Framework\View\Design\Fallback\RulePool::TYPE_FILE => [
                'no theme', 'no area', 'no namespace', 'no module',
            ],
            \Magento\Framework\View\Design\Fallback\RulePool::TYPE_TEMPLATE_FILE => [
                'no theme', 'no area', 'no namespace', 'no module',
            ],
            \Magento\Framework\View\Design\Fallback\RulePool::TYPE_STATIC_FILE => [
                'no theme', 'no area', 'no namespace', 'no module',
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
        return [
            'locale' => [
                \Magento\Framework\View\Design\Fallback\RulePool::TYPE_LOCALE_FILE,
                [],
                [
                    DirectoryList::THEMES . '/area/current_theme_path',
                    DirectoryList::THEMES . '/area/parent_theme_path',
                ],
            ],
            'file, modular' => [
                \Magento\Framework\View\Design\Fallback\RulePool::TYPE_FILE,
                [],
                [
                    DirectoryList::THEMES . '/area/current_theme_path/namespace_module',
                    DirectoryList::THEMES . '/area/parent_theme_path/namespace_module',
                    DirectoryList::MODULES . '/namespace/module/view/area',
                    DirectoryList::MODULES . '/namespace/module/view/base',
                ],
            ],
            'file, non-modular' => [
                \Magento\Framework\View\Design\Fallback\RulePool::TYPE_FILE,
                ['namespace' => null, 'module' => null],
                [
                    DirectoryList::THEMES . '/area/current_theme_path',
                    DirectoryList::THEMES . '/area/parent_theme_path',
                ],
            ],

            'template, modular' => [
                \Magento\Framework\View\Design\Fallback\RulePool::TYPE_TEMPLATE_FILE,
                [],
                [
                    DirectoryList::THEMES . '/area/current_theme_path/namespace_module/templates',
                    DirectoryList::THEMES . '/area/parent_theme_path/namespace_module/templates',
                    DirectoryList::MODULES . '/namespace/module/view/area/templates',
                    DirectoryList::MODULES . '/namespace/module/view/base/templates',
                ],
            ],
            'template, non-modular' => [
                \Magento\Framework\View\Design\Fallback\RulePool::TYPE_TEMPLATE_FILE,
                ['namespace' => null, 'module' => null],
                [
                    DirectoryList::THEMES . '/area/current_theme_path/templates',
                    DirectoryList::THEMES . '/area/parent_theme_path/templates',
                ],
            ],
            'template, non-modular-magento-core' => [
                \Magento\Framework\View\Design\Fallback\RulePool::TYPE_TEMPLATE_FILE,
                ['namespace' => 'Magento', 'module' => 'Core'],
                [
                    DirectoryList::THEMES . '/area/current_theme_path/Magento_Core/templates',
                    DirectoryList::THEMES . '/area/parent_theme_path/Magento_Core/templates',
                    DirectoryList::MODULES . '/Magento/Core/view/area/templates',
                    DirectoryList::MODULES . '/Magento/Core/view/base/templates',
                ],
            ],

            'view, modular localized' => [
                \Magento\Framework\View\Design\Fallback\RulePool::TYPE_STATIC_FILE,
                [],
                [
                    DirectoryList::THEMES . '/area/current_theme_path/namespace_module/web/i18n/en_US',
                    DirectoryList::THEMES . '/area/current_theme_path/namespace_module/web',
                    DirectoryList::THEMES . '/area/parent_theme_path/namespace_module/web/i18n/en_US',
                    DirectoryList::THEMES . '/area/parent_theme_path/namespace_module/web',
                    DirectoryList::MODULES . '/namespace/module/view/area/web/i18n/en_US',
                    DirectoryList::MODULES . '/namespace/module/view/base/web/i18n/en_US',
                    DirectoryList::MODULES . '/namespace/module/view/area/web',
                    DirectoryList::MODULES . '/namespace/module/view/base/web',
                ],
            ],
            'view, modular non-localized' => [
                \Magento\Framework\View\Design\Fallback\RulePool::TYPE_STATIC_FILE,
                ['locale' => null],
                [
                    DirectoryList::THEMES . '/area/current_theme_path/namespace_module/web',
                    DirectoryList::THEMES . '/area/parent_theme_path/namespace_module/web',
                    DirectoryList::MODULES . '/namespace/module/view/area/web',
                    DirectoryList::MODULES . '/namespace/module/view/base/web',
                ],
            ],
            'view, non-modular localized' => [
                \Magento\Framework\View\Design\Fallback\RulePool::TYPE_STATIC_FILE,
                ['module' => null, 'namespace' => null],
                [
                    DirectoryList::THEMES . '/area/current_theme_path/web/i18n/en_US',
                    DirectoryList::THEMES . '/area/current_theme_path/web',
                    DirectoryList::THEMES . '/area/parent_theme_path/web/i18n/en_US',
                    DirectoryList::THEMES . '/area/parent_theme_path/web',
                    DirectoryList::LIB_WEB,
                ],
            ],
            'view, non-modular non-localized' => [
                \Magento\Framework\View\Design\Fallback\RulePool::TYPE_STATIC_FILE,
                ['module' => null, 'namespace' => null, 'locale' => null],
                [
                    DirectoryList::THEMES . '/area/current_theme_path/web',
                    DirectoryList::THEMES . '/area/parent_theme_path/web',
                    DirectoryList::LIB_WEB,
                ],
            ],
        ];
    }
}
