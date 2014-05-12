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
        $object->getPatternDirs(array());
    }

    public function testGetPatternDirs()
    {
        $parentTheme = $this->getMockForAbstractClass('Magento\Framework\View\Design\ThemeInterface');
        $parentTheme->expects($this->any())->method('getThemePath')->will($this->returnValue('package/parent_theme'));

        $theme = $this->getMockForAbstractClass('Magento\Framework\View\Design\ThemeInterface');
        $theme->expects($this->any())->method('getThemePath')->will($this->returnValue('package/current_theme'));
        $theme->expects($this->any())->method('getParentTheme')->will($this->returnValue($parentTheme));

        $ruleDirsMap = array(
            array(
                array('theme_path' => 'package/current_theme'),
                array('package/current_theme/path/one', 'package/current_theme/path/two')
            ),
            array(
                array('theme_path' => 'package/parent_theme'),
                array('package/parent_theme/path/one', 'package/parent_theme/path/two')
            )
        );
        $rule = $this->getMockForAbstractClass('Magento\Framework\View\Design\Fallback\Rule\RuleInterface');
        $rule->expects($this->any())->method('getPatternDirs')->will($this->returnValueMap($ruleDirsMap));
        /** @var $rule RuleInterface */
        $object = new Theme($rule);

        $expectedResult = array(
            'package/current_theme/path/one',
            'package/current_theme/path/two',
            'package/parent_theme/path/one',
            'package/parent_theme/path/two'
        );
        $this->assertEquals($expectedResult, $object->getPatternDirs(array('theme' => $theme)));
    }
}
