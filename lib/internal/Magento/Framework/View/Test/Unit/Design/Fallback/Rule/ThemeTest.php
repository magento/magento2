<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Test\Unit\Design\Fallback\Rule;

use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Component\ComponentRegistrarInterface;
use Magento\Framework\View\Design\Fallback\Rule\RuleInterface;
use \Magento\Framework\View\Design\Fallback\Rule\Theme;

class ThemeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var RuleInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $rule;

    /**
     * @var ComponentRegistrarInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $componentRegistrar;

    /**
     * @var Theme
     */
    private $model;

    protected function setUp()
    {
        $this->rule = $this->getMockForAbstractClass('\Magento\Framework\View\Design\Fallback\Rule\RuleInterface');
        $this->componentRegistrar = $this->getMockForAbstractClass(
            '\Magento\Framework\Component\ComponentRegistrarInterface'
        );
        $this->model = new Theme($this->rule, $this->componentRegistrar);
    }
    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Parameter "theme" should be specified and should implement the theme interface
     */
    public function testGetPatternDirsException()
    {
        $this->model->getPatternDirs([]);
    }

    public function testGetPatternDirs()
    {
        $parentTheme = $this->getMockForAbstractClass('Magento\Framework\View\Design\ThemeInterface');
        $parentTheme->expects($this->any())->method('getFullPath')->will($this->returnValue('package/parent_theme'));

        $theme = $this->getMockForAbstractClass('Magento\Framework\View\Design\ThemeInterface');
        $theme->expects($this->any())->method('getFullPath')->will($this->returnValue('package/current_theme'));
        $theme->expects($this->any())->method('getParentTheme')->will($this->returnValue($parentTheme));

        $this->componentRegistrar->expects($this->any())
            ->method('getPath')
            ->will($this->returnValueMap([
                [ComponentRegistrar::THEME, 'package/parent_theme', '/path/to/parent/theme'],
                [ComponentRegistrar::THEME, 'package/current_theme', '/path/to/current/theme'],
            ]));

        $ruleDirsMap = [
            [
                ['theme_dir' => '/path/to/current/theme'],
                ['package/current_theme/path/one', 'package/current_theme/path/two'],
            ],
            [
                ['theme_dir' => '/path/to/parent/theme'],
                ['package/parent_theme/path/one', 'package/parent_theme/path/two']
            ],
        ];
        $this->rule->expects($this->any())->method('getPatternDirs')->will($this->returnValueMap($ruleDirsMap));
        $expectedResult = [
            'package/current_theme/path/one',
            'package/current_theme/path/two',
            'package/parent_theme/path/one',
            'package/parent_theme/path/two',
        ];
        $this->assertEquals($expectedResult, $this->model->getPatternDirs(['theme' => $theme]));
    }
}
