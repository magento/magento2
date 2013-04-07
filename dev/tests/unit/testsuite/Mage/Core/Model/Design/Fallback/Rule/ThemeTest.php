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
 * @category    Magento
 * @package     Mage_Core
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Core_Model_Design_Fallback_Rule_ThemeTest extends PHPUnit_Framework_TestCase
{
    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Each element should implement Mage_Core_Model_Design_Fallback_Rule_RuleInterface
     */
    public function testConstructExceptionNotAnInterface()
    {
        $rules = array('not an interface');
        new Mage_Core_Model_Design_Fallback_Rule_Theme($rules);
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage $params["theme"] should be passed and should implement Mage_Core_Model_ThemeInterface
     */
    public function testGetPatternDirsException()
    {
        $simpleRuleMockOne = $this->getMock(
            'Mage_Core_Model_Design_Fallback_Rule_Simple',
            array(),
            array('pattern')
        );

        $model = new Mage_Core_Model_Design_Fallback_Rule_Theme(array($simpleRuleMockOne));
        $model->getPatternDirs(array());
    }

    public function testGetPatternDirs()
    {
        $parentThemePath = 'parent_package/parent_theme';
        $parentTheme = $this->getMock('Mage_Core_Model_Theme', array('getThemePath'), array(), '', false);
        $parentTheme->expects($this->any())
            ->method('getThemePath')
            ->will($this->returnValue($parentThemePath));

        $themePath = 'package/theme';
        $theme = $this->getMock('Mage_Core_Model_Theme', array('getThemePath', 'getParentTheme'), array(), '', false);
        $theme->expects($this->any())
            ->method('getThemePath')
            ->will($this->returnValue($themePath));

        $theme->expects($this->any())
            ->method('getParentTheme')
            ->will($this->returnValue($parentTheme));

        $patternOne = '<theme_path> one';
        $patternTwo = '<theme_path> two';

        $mapOne = array(
            array(
                array('theme' => $theme, 'theme_path' => $theme->getThemePath()),
                array('package/theme one')
            ),
            array(
                array('theme' => $theme, 'theme_path' => $parentTheme->getThemePath()),
                array('parent_package/parent_theme one')
            )
        );

        $mapTwo = array(
            array(
                array('theme' => $theme, 'theme_path' => $theme->getThemePath()),
                array('package/theme two')
            ),
            array(
                array('theme' => $theme, 'theme_path' => $parentTheme->getThemePath()),
                array('parent_package/parent_theme two')
            )
        );

        $simpleRuleMockOne = $this->getMock(
            'Mage_Core_Model_Design_Fallback_Rule_Simple',
            array('getPatternDirs'),
            array($patternOne)
        );

        $simpleRuleMockTwo = $this->getMock(
            'Mage_Core_Model_Design_Fallback_Rule_Simple',
            array('getPatternDirs'),
            array($patternTwo)
        );

        $simpleRuleMockOne->expects($this->any())
            ->method('getPatternDirs')
            ->will($this->returnValueMap($mapOne));

        $simpleRuleMockTwo->expects($this->any())
            ->method('getPatternDirs')
            ->will($this->returnValueMap($mapTwo));

        $params = array('theme' => $theme);
        $model = new Mage_Core_Model_Design_Fallback_Rule_Theme(array($simpleRuleMockOne, $simpleRuleMockTwo));

        $expectedResult = array(
            'package/theme one',
            'package/theme two',
            'parent_package/parent_theme one',
            'parent_package/parent_theme two'
        );

        $this->assertEquals($expectedResult, $model->getPatternDirs($params));
    }
}
