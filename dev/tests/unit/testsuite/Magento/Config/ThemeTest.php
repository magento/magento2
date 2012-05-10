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
 * @package     Framework
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Magento_Config_ThemeTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Magento_Config_Theme
     */
    protected static $_model = null;

    public static function setUpBeforeClass()
    {
        self::$_model = new Magento_Config_Theme(glob(__DIR__ . '/_files/packages/*/*/theme.xml'));
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testConstructException()
    {
        new Magento_Config_Theme(array());
    }

    public function testGetSchemaFile()
    {
        $this->assertFileExists(self::$_model->getSchemaFile());
    }

    /**
     * @param string $package
     * @param mixed $expected
     * @dataProvider getPackageTitleDataProvider
     */
    public function testGetPackageTitle($package, $expected)
    {
        $this->assertSame($expected, self::$_model->getPackageTitle($package));
    }

    /**
     * @return array
     */
    public function getPackageTitleDataProvider()
    {
        return array(
            array('default', 'Default'),
            array('test',    'Test'),
        );
    }

    /**
     * @expectedException Magento_Exception
     */
    public function testGetPackageTitleException()
    {
        self::$_model->getPackageTitle('invalid');
    }

    /**
     * @param string $package
     * @param string $theme
     * @param mixed $expected
     * @dataProvider getThemeTitleDataProvider
     */
    public function testGetThemeTitle($package, $theme, $expected)
    {
        $this->assertSame($expected, self::$_model->getThemeTitle($package, $theme));
    }

    /**
     * @return array
     */
    public function getThemeTitleDataProvider()
    {
        return array(
            array('default', 'default', 'Default'),
            array('default', 'test',    'Test'),
        );
    }

    /**
     * @param string $package
     * @param string $theme
     * @param mixed $expected
     * @dataProvider getParentThemeDataProvider
     */
    public function testGetParentTheme($package, $theme, $expected)
    {
        $this->assertSame($expected, self::$_model->getParentTheme($package, $theme));
    }

    /**
     * @return array
     */
    public function getParentThemeDataProvider()
    {
        return array(
            array('default', 'default', null),
            array('default', 'test',    'default'),
            array('default', 'test2',   'test'),
        );
    }

    /**
     * @dataProvider getCompatibleVersionsDataProvider
     */
    public function testGetCompatibleVersions($package, $theme, $versions)
    {
        $this->assertEquals($versions, self::$_model->getCompatibleVersions($package, $theme));
    }

    public function getCompatibleVersionsDataProvider()
    {
        return array(
            array('test', 'default', array('from' => '2.0.0.0-dev1', 'to' => '*')),
            array('default', 'test', array('from' => '2.0.0.0', 'to' => '*')),
        );
    }

    /**
     * @param string $getter
     * @param string $package
     * @param string $theme
     * @dataProvider ensureThemeExistsExceptionDataProvider
     * @expectedException Magento_Exception
     */
    public function testEnsureThemeExistsException($getter, $package, $theme)
    {
        self::$_model->$getter($package, $theme);
    }

    /**
     * @return array
     */
    public function ensureThemeExistsExceptionDataProvider()
    {
        $result = array();
        foreach (array('getThemeTitle', 'getParentTheme', 'getCompatibleVersions') as $getter) {
            $result[] = array($getter, 'invalid', 'invalid');
            $result[] = array($getter, 'default', 'invalid');
            $result[] = array($getter, 'invalid', 'default');
        }
        return $result;
    }
}
