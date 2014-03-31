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
namespace Magento\View\Design\Fallback;

/**
 * Factory Test
 *
 * @package Magento\View\Design\Fallback
 */
class FactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Factory
     */
    protected $model;

    /**
     * @var array
     */
    protected $defaultParams;

    protected function setUp()
    {
        $filesystemMock = $this->getMock(
            '\Magento\App\Filesystem',
            array('getPath', 'getDirectoryRead', '__wakeup'),
            array(
                'dir' => array(
                    \Magento\App\Filesystem::THEMES_DIR => 'themes',
                    \Magento\App\Filesystem::MODULES_DIR => 'modules',
                    \Magento\App\Filesystem::PUB_LIB_DIR => 'pub_lib'
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
                    \Magento\App\Filesystem::THEMES_DIR => 'themes',
                    \Magento\App\Filesystem::MODULES_DIR => 'modules',
                    \Magento\App\Filesystem::PUB_LIB_DIR => 'pub_lib'
                )
            )
        );

        $this->model = new Factory($filesystemMock);

        $parentTheme = $this->getMockForAbstractClass('Magento\View\Design\ThemeInterface');
        $parentTheme->expects($this->any())->method('getThemePath')->will($this->returnValue('parent_theme_path'));

        $theme = $this->getMockForAbstractClass('Magento\View\Design\ThemeInterface');
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

    public function testCreateLocaleFileRule()
    {
        $actualResult = $this->model->createLocaleFileRule();
        $this->assertInstanceOf('\Magento\View\Design\Fallback\Rule\RuleInterface', $actualResult);
        $this->assertNotSame($actualResult, $this->model->createLocaleFileRule());
    }

    public function testCreateLocaleFileRuleGetPatternDirs()
    {
        $expectedResult = array('/area/current_theme_path/i18n/en_US', '/area/parent_theme_path/i18n/en_US');
        $this->assertSame($expectedResult, $this->model->createLocaleFileRule()->getPatternDirs($this->defaultParams));
    }

    /**
     * @param array $overriddenParams
     * @param string $expectedErrorMessage
     * @dataProvider createLocaleFileRuleGetPatternDirsExceptionDataProvider
     */
    public function testCreateLocaleFileRuleGetPatternDirsException(array $overriddenParams, $expectedErrorMessage)
    {
        $this->setExpectedException('InvalidArgumentException', $expectedErrorMessage);
        $this->model->createLocaleFileRule()->getPatternDirs($overriddenParams + $this->defaultParams);
    }

    /**
     * @return array
     */
    public function createLocaleFileRuleGetPatternDirsExceptionDataProvider()
    {
        return array(
            'no theme' => array(
                array('theme' => null),
                'Parameter "theme" should be specified and should implement the theme interface'
            ),
            'no area' => array(array('area' => null), "Required parameter 'area' was not passed"),
            'no locale' => array(array('locale' => null), "Required parameter 'locale' was not passed")
        );
    }

    public function testCreateFileRule()
    {
        $actualResult = $this->model->createFileRule();
        $this->assertInstanceOf('\Magento\View\Design\Fallback\Rule\RuleInterface', $actualResult);
        $this->assertNotSame($actualResult, $this->model->createFileRule());
    }

    /**
     * @param array $overriddenParams
     * @param array $expectedResult
     * @dataProvider createFileRuleGetPatternDirsDataProvider
     */
    public function testCreateFileRuleGetPatternDirs(array $overriddenParams, array $expectedResult)
    {
        $actualResult = $this->model->createFileRule()->getPatternDirs($overriddenParams + $this->defaultParams);
        $this->assertEquals($expectedResult, $actualResult);
    }

    /**
     * @return array
     */
    public function createFileRuleGetPatternDirsDataProvider()
    {
        return array(
            'modular' => array(
                array(),
                array(
                    '/area/current_theme_path/namespace_module',
                    '/area/parent_theme_path/namespace_module',
                    '/namespace/module/view/area'
                )
            ),
            'non-modular' => array(
                array('namespace' => null, 'module' => null),
                array('/area/current_theme_path', '/area/parent_theme_path')
            )
        );
    }

    /**
     * @param array $overriddenParams
     * @param $expectedErrorMessage
     * @dataProvider createRuleGetPatternDirsExceptionDataProvider
     */
    public function testCreateFileRuleGetPatternDirsException(array $overriddenParams, $expectedErrorMessage)
    {
        $this->setExpectedException('InvalidArgumentException', $expectedErrorMessage);
        $this->model->createFileRule()->getPatternDirs($overriddenParams + $this->defaultParams);
    }

    public function testCreateViewFileRule()
    {
        $actualResult = $this->model->createViewFileRule();
        $this->assertInstanceOf('\Magento\View\Design\Fallback\Rule\RuleInterface', $actualResult);
        $this->assertNotSame($actualResult, $this->model->createViewFileRule());
    }

    /**
     * @param array $overriddenParams
     * @param array $expectedResult
     * @dataProvider createViewFileRuleGetPatternDirsDataProvider
     */
    public function testCreateViewFileRuleGetPatternDirs(array $overriddenParams, array $expectedResult)
    {
        $actualResult = $this->model->createViewFileRule()->getPatternDirs($overriddenParams + $this->defaultParams);
        $this->assertEquals($expectedResult, $actualResult);
    }

    /**
     * @return array
     */
    public function createViewFileRuleGetPatternDirsDataProvider()
    {
        return array(
            'modular localized' => array(
                array(),
                array(
                    '/area/current_theme_path/i18n/en_US/namespace_module',
                    '/area/current_theme_path/namespace_module',
                    '/area/parent_theme_path/i18n/en_US/namespace_module',
                    '/area/parent_theme_path/namespace_module',
                    '/namespace/module/view/area/i18n/en_US',
                    '/namespace/module/view/area'
                )
            ),
            'modular non-localized' => array(
                array('locale' => null),
                array(
                    '/area/current_theme_path/namespace_module',
                    '/area/parent_theme_path/namespace_module',
                    '/namespace/module/view/area'
                )
            ),
            'non-modular localized' => array(
                array('module' => null, 'namespace' => null),
                array(
                    '/area/current_theme_path/i18n/en_US',
                    '/area/current_theme_path',
                    '/area/parent_theme_path/i18n/en_US',
                    '/area/parent_theme_path',
                    ''
                )
            ),
            'non-modular non-localized' => array(
                array('module' => null, 'namespace' => null, 'locale' => null),
                array('/area/current_theme_path', '/area/parent_theme_path', '')
            )
        );
    }

    /**
     * @param array $overriddenParams
     * @param $expectedErrorMessage
     * @dataProvider createRuleGetPatternDirsExceptionDataProvider
     */
    public function testCreateViewFileRuleGetPatternDirsException(array $overriddenParams, $expectedErrorMessage)
    {
        $this->setExpectedException('InvalidArgumentException', $expectedErrorMessage);
        $this->model->createViewFileRule()->getPatternDirs($overriddenParams + $this->defaultParams);
    }

    /**
     * @return array
     */
    public function createRuleGetPatternDirsExceptionDataProvider()
    {
        return array(
            'no theme' => array(
                array('theme' => null),
                'Parameter "theme" should be specified and should implement the theme interface'
            ),
            'no area' => array(array('area' => null), "Required parameter 'area' was not passed"),
            'no namespace' => array(
                array('namespace' => null),
                "Parameters 'namespace' and 'module' should either be both set or unset"
            ),
            'no module' => array(
                array('module' => null),
                "Parameters 'namespace' and 'module' should either be both set or unset"
            )
        );
    }
}
