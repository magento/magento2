<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Design\Fallback\Rule;

/**
 * Theme Test
 *
 */
class ThemeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Parameter "theme" should be specified and should implement the theme interface
     */
    public function testGetPatternDirsException()
    {
        $rule = $this->getMockForAbstractClass('\Magento\Framework\View\Design\Fallback\Rule\RuleInterface');
        /** @var $rule RuleInterface */
        $object = new Theme($rule);
        $object->getPatternDirs([]);
    }

    public function testGetPatternDirs()
    {
        $parentTheme = $this->getMockForAbstractClass('Magento\Framework\View\Design\ThemeInterface');
        $parentTheme->expects($this->any())->method('getThemePath')->will($this->returnValue('package/parent_theme'));

        $theme = $this->getMockForAbstractClass('Magento\Framework\View\Design\ThemeInterface');
        $theme->expects($this->any())->method('getThemePath')->will($this->returnValue('package/current_theme'));
        $theme->expects($this->any())->method('getParentTheme')->will($this->returnValue($parentTheme));

        $ruleDirsMap = [
            [
                ['theme_path' => 'package/current_theme'],
                ['package/current_theme/path/one', 'package/current_theme/path/two'],
            ],
            [
                ['theme_path' => 'package/parent_theme'],
                ['package/parent_theme/path/one', 'package/parent_theme/path/two']
            ],
        ];
        $rule = $this->getMockForAbstractClass('Magento\Framework\View\Design\Fallback\Rule\RuleInterface');
        $rule->expects($this->any())->method('getPatternDirs')->will($this->returnValueMap($ruleDirsMap));
        /** @var $rule RuleInterface */
        $object = new Theme($rule);

        $expectedResult = [
            'package/current_theme/path/one',
            'package/current_theme/path/two',
            'package/parent_theme/path/one',
            'package/parent_theme/path/two',
        ];
        $this->assertEquals($expectedResult, $object->getPatternDirs(['theme' => $theme]));
    }
}
