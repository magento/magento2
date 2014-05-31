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
namespace Magento\Framework\View\Design\Fallback;

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
        $filesystemMock = $this->getMock(
            '\Magento\Framework\App\Filesystem',
            array('getPath', 'getDirectoryRead', '__wakeup'),
            array(
                'dir' => array(
                    \Magento\Framework\App\Filesystem::THEMES_DIR => 'themes',
                    \Magento\Framework\App\Filesystem::MODULES_DIR => 'modules',
                    \Magento\Framework\App\Filesystem::LIB_WEB => 'lib_web',
                )
            ),
            '',
            false
        );
        $filesystemMock->expects(
            $this->any()
        )->method(
            'getPath'
        )->will(
            $this->returnValueMap(
                array(
                    \Magento\Framework\App\Filesystem::THEMES_DIR => 'themes',
                    \Magento\Framework\App\Filesystem::MODULES_DIR => 'modules',
                    \Magento\Framework\App\Filesystem::LIB_WEB => 'lib_web',
                )
            )
        );

        $this->model = new RulePool($filesystemMock);

        $parentTheme = $this->getMockForAbstractClass('Magento\Framework\View\Design\ThemeInterface');
        $parentTheme->expects($this->any())->method('getThemePath')->will($this->returnValue('parent_theme_path'));

        $theme = $this->getMockForAbstractClass('Magento\Framework\View\Design\ThemeInterface');
        $theme->expects($this->any())->method('getThemePath')->will($this->returnValue('current_theme_path'));
        $theme->expects($this->any())->method('getParentTheme')->will($this->returnValue($parentTheme));

        $this->defaultParams = array(
            'area' => 'area',
            'theme' => $theme,
            'namespace' => 'namespace',
            'module' => 'module',
            'locale' => 'en_US'
        );
    }

    protected function tearDown()
    {
        $this->model = null;
        $this->defaultParams = array();
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
                'no theme', 'no area'
            ],
            \Magento\Framework\View\Design\Fallback\RulePool::TYPE_FILE => [
                'no theme', 'no area', 'no namespace', 'no module'
            ],
            \Magento\Framework\View\Design\Fallback\RulePool::TYPE_TEMPLATE_FILE => [
                'no theme', 'no area', 'no namespace', 'no module'
            ],
            \Magento\Framework\View\Design\Fallback\RulePool::TYPE_STATIC_FILE => [
                'no theme', 'no area', 'no namespace', 'no module'
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
     */
    public function getPatternDirsDataProvider()
    {
        return [
            'locale' => [
                \Magento\Framework\View\Design\Fallback\RulePool::TYPE_LOCALE_FILE,
                [],
                ['/area/current_theme_path', '/area/parent_theme_path'],
            ],
            'file, modular' => [
                \Magento\Framework\View\Design\Fallback\RulePool::TYPE_FILE,
                [],
                [
                    '/area/current_theme_path/namespace_module',
                    '/area/parent_theme_path/namespace_module',
                    '/namespace/module/view/area',
                    '/namespace/module/view/base',
                ],
            ],
            'file, non-modular' => [
                \Magento\Framework\View\Design\Fallback\RulePool::TYPE_FILE,
                ['namespace' => null, 'module' => null],
                ['/area/current_theme_path', '/area/parent_theme_path',],
            ],

            'template, modular' => [
                \Magento\Framework\View\Design\Fallback\RulePool::TYPE_TEMPLATE_FILE,
                [],
                [
                    '/area/current_theme_path/namespace_module/templates',
                    '/area/parent_theme_path/namespace_module/templates',
                    '/namespace/module/view/area/templates',
                    '/namespace/module/view/base/templates',
                ],
            ],
            'template, non-modular' => [
                \Magento\Framework\View\Design\Fallback\RulePool::TYPE_TEMPLATE_FILE,
                ['namespace' => null, 'module' => null],
                [
                    '/area/current_theme_path/templates',
                    '/area/parent_theme_path/templates',
                ],
            ],
            'template, non-modular-magento-core' => [
                \Magento\Framework\View\Design\Fallback\RulePool::TYPE_TEMPLATE_FILE,
                ['namespace' => 'Magento', 'module' => 'Core'],
                [
                    '/area/current_theme_path/Magento_Core/templates',
                    '/area/parent_theme_path/Magento_Core/templates',
                    '/Magento/Core/view/area/templates',
                    '/Magento/Core/view/base/templates',
                ],
            ],

            'view, modular localized' => [
                \Magento\Framework\View\Design\Fallback\RulePool::TYPE_STATIC_FILE,
                [],
                [
                    '/area/current_theme_path/namespace_module/web/i18n/en_US',
                    '/area/current_theme_path/namespace_module/web',
                    '/area/parent_theme_path/namespace_module/web/i18n/en_US',
                    '/area/parent_theme_path/namespace_module/web',
                    '/namespace/module/view/area/web/i18n/en_US',
                    '/namespace/module/view/base/web/i18n/en_US',
                    '/namespace/module/view/area/web',
                    '/namespace/module/view/base/web',
                ],
            ],
            'view, modular non-localized' => [
                \Magento\Framework\View\Design\Fallback\RulePool::TYPE_STATIC_FILE,
                ['locale' => null],
                [
                    '/area/current_theme_path/namespace_module/web',
                    '/area/parent_theme_path/namespace_module/web',
                    '/namespace/module/view/area/web',
                    '/namespace/module/view/base/web',
                ],
            ],
            'view, non-modular localized' => [
                \Magento\Framework\View\Design\Fallback\RulePool::TYPE_STATIC_FILE,
                ['module' => null, 'namespace' => null],
                [
                    '/area/current_theme_path/web/i18n/en_US',
                    '/area/current_theme_path/web',
                    '/area/parent_theme_path/web/i18n/en_US',
                    '/area/parent_theme_path/web',
                    '',
                ],
            ],
            'view, non-modular non-localized' => [
                \Magento\Framework\View\Design\Fallback\RulePool::TYPE_STATIC_FILE,
                ['module' => null, 'namespace' => null, 'locale' => null],
                [
                    '/area/current_theme_path/web',
                    '/area/parent_theme_path/web',
                    '',
                ],
            ],
        ];
    }
}
