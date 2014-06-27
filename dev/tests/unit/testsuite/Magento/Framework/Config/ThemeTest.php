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
namespace Magento\Framework\Config;

class ThemeTest extends \PHPUnit_Framework_TestCase
{
    public function testGetSchemaFile()
    {
        $config = new \Magento\Framework\Config\Theme(
            file_get_contents(__DIR__ . '/_files/area/default_default/theme.xml')
        );
        $this->assertFileExists($config->getSchemaFile());
    }

    /**
     * @param string $themePath
     * @param mixed $expected
     * @dataProvider getThemeTitleDataProvider
     */
    public function testGetThemeTitle($themePath, $expected)
    {
        $config = new \Magento\Framework\Config\Theme(
            file_get_contents(__DIR__ . "/_files/area/{$themePath}/theme.xml")
        );
        $this->assertSame($expected, $config->getThemeTitle());
    }

    /**
     * @return array
     */
    public function getThemeTitleDataProvider()
    {
        return array(array('default_default', 'Default'), array('default_test', 'Test'));
    }

    /**
     * @param string $themePath
     * @param mixed $expected
     * @dataProvider getParentThemeDataProvider
     */
    public function testGetParentTheme($themePath, $expected)
    {
        $config = new \Magento\Framework\Config\Theme(
            file_get_contents(__DIR__ . "/_files/area/{$themePath}/theme.xml")
        );
        $this->assertSame($expected, $config->getParentTheme());
    }

    /**
     * @return array
     */
    public function getParentThemeDataProvider()
    {
        return array(
            array('default_default', null),
            array('default_test', array('default_default')),
            array('default_test2', array('default_test')),
            array('test_external_package_descendant', array('default_test2'))
        );
    }

    /**
     * @param string $themePath
     * @param array $expected
     * @dataProvider dataGetterDataProvider
     */
    public function testDataGetter($themePath, $expected)
    {
        $expected = reset($expected);
        $config = new \Magento\Framework\Config\Theme(
            file_get_contents(__DIR__ . "/_files/area/$themePath/theme.xml")
        );
        $this->assertSame($expected['version'], $config->getThemeVersion());
        $this->assertSame($expected['media'], $config->getMedia());
    }

    /**
     * @return array
     */
    public function dataGetterDataProvider()
    {
        return array(
            array(
                'default_default',
                array(array(
                    'version' => '0.1.0',
                    'media' => array('preview_image' => 'media/default_default.jpg'),
                ))),
            array(
                'default_test',
                array(array(
                    'version' => '0.1.1',
                    'media' => array('preview_image' => ''),
                ))),
            array(
                'default_test2',
                array(array(
                    'version' => '0.1.2',
                    'media' => array('preview_image' => ''),
                ))),
            array(
                'test_default',
                array(array(
                    'version' => '0.1.3',
                    'media' => array('preview_image' => 'media/test_default.jpg'),
                ))),
        );
    }
}
