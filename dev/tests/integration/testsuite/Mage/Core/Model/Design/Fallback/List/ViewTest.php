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

class Mage_Core_Model_Design_Fallback_List_ViewTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Core_Model_Design_Fallback_List_File
     */
    protected $_model;

    public function setUp()
    {
        $this->_model = Mage::getObjectManager()->create('Mage_Core_Model_Design_Fallback_List_View');
    }

    /**
     * @dataProvider getPatternDirsDataProvider
     */
    public function testGetPatternDirs($namespace, $module, $locale, $expectedIndexes)
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
        );

        $params['namespace'] = $namespace;
        $params['module'] = $module;
        $params['locale'] = $locale;

        $actualResult = $this->_model->getPatternDirs($params);

        /**
         * This is array of all possible paths. Data provider returns indexes of this array as an expected result
         * Indexes added for easier reading
         */
        $fullExpectedResult = array(
            0 => $dir->getDir(Mage_Core_Model_Dir::THEMES) . '/area/theme_path/locale/locale',
            1 => $dir->getDir(Mage_Core_Model_Dir::THEMES) . '/area/theme_path',
            2 => $dir->getDir(Mage_Core_Model_Dir::THEMES) . '/area/theme_path/locale/locale/namespace_module',
            3 => $dir->getDir(Mage_Core_Model_Dir::THEMES) . '/area/theme_path/namespace_module',
            4 => $dir->getDir(Mage_Core_Model_Dir::THEMES) . '/area/parent_theme_path/locale/locale',
            5 => $dir->getDir(Mage_Core_Model_Dir::THEMES) . '/area/parent_theme_path',
            6 => $dir->getDir(Mage_Core_Model_Dir::THEMES) . '/area/parent_theme_path/locale/locale/namespace_module',
            7 => $dir->getDir(Mage_Core_Model_Dir::THEMES) . '/area/parent_theme_path/namespace_module',
            8 => $dir->getDir(Mage_Core_Model_Dir::MODULES) . '/namespace/module/view/area/locale/locale',
            9 => $dir->getDir(Mage_Core_Model_Dir::MODULES) . '/namespace/module/view/area',
            10 => $dir->getDir(Mage_Core_Model_Dir::PUB_LIB),
        );

        $expectedArray = array();
        foreach ($expectedIndexes as $index) {
            $expectedArray[] = $fullExpectedResult[$index];
        }

        $this->assertSame($expectedArray, $actualResult);
    }

    public function getPatternDirsDataProvider()
    {
        return array(
            'all parameters passed' => array(
                'namespace' => 'namespace',
                'module' => 'module',
                'locale' => 'locale',
                range(0, 10)
            ),
            'no module parameter passed' => array(
                'namespace' => 'namespace',
                'module' => null,
                'locale' => 'locale',
                array(0, 1, 4, 5, 10)
            ),
            'no namespace parameter passed' => array(
                'namespace' => null,
                'module' => 'module',
                'locale' => 'locale',
                array(0, 1, 4, 5, 10)
            ),
            'no locale parameter passed' => array(
                'namespace' => 'namespace',
                'module' => 'module',
                'locale' => null,
                array(1, 3, 5, 7, 9, 10)
            ),
            'no optional parameter passed' => array(
                'namespace' => null,
                'module' => null,
                'locale' => null,
                array(1, 5, 10)
            ),
        );
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
            'namespace' => 'namespace',
            'module' => 'module',
            'locale' => 'locale',
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
        );
    }
}
