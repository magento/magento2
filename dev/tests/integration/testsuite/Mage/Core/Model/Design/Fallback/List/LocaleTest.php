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
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Core_Model_Design_Fallback_List_LocaleTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Core_Model_Design_Fallback_List_Locale
     */
    protected $_model;

    public function setUp()
    {
        $this->_model = Mage::getObjectManager()->create('Mage_Core_Model_Design_Fallback_List_Locale');
    }

    public function testGetPatternDirs()
    {
        $dir = Mage::getObjectManager()->get('Mage_Core_Model_Dir');

        $parentTheme = Mage::getObjectManager()->create('Mage_Core_Model_Theme');
        $parentTheme->setThemePath('parent_theme_path');

        $theme = Mage::getObjectManager()->create('Mage_Core_Model_Theme');
        $theme->setThemePath('theme_path');
        $theme->setParentTheme($parentTheme);

        $params = array(
            'theme' => $theme,
            'area' => 'area',
            'locale' => 'locale'
        );

        $actualResult = $this->_model->getPatternDirs($params);

        $expectedResult = array(
            $dir->getDir(Mage_Core_Model_Dir::THEMES) . '/area/theme_path/locale/locale',
            $dir->getDir(Mage_Core_Model_Dir::THEMES) . '/area/parent_theme_path/locale/locale',
        );

        $this->assertSame($expectedResult, $actualResult);
    }

    /**
     * @dataProvider getPatternDirsExceptionDataProvider
     */
    public function testGetPatternDirsException($setParams, $expectedMessage)
    {
        $this->setExpectedException('InvalidArgumentException', $expectedMessage);

        $theme = Mage::getObjectManager()->create('Mage_Core_Model_Theme');
        $theme->setThemePath('theme_path');

        $params = array(
            'theme' => $theme,
            'area' => 'area',
            'locale' => 'locale'
        );
        $params = array_merge($params, $setParams);

        $this->_model->getPatternDirs($params);
    }

    public function getPatternDirsExceptionDataProvider()
    {
        return array(
            'No theme' => array(
                array('theme' => null),
                '$params["theme"] should be passed and should implement Mage_Core_Model_ThemeInterface'
            ),
            'No area' => array(
                array('area' => null),
                'Required parameter \'area\' was not passed'
            ),
            'No locale' => array(
                array('locale' => null),
                'Required parameter \'locale\' was not passed'
            ),
        );
    }
}
