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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Config;

class ThemeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testConstructException()
    {
        new \Magento\Config\Theme(array());
    }

    public function testGetSchemaFile()
    {
        $config = new \Magento\Config\Theme(array(
            sprintf('%s/_files/area/%s/theme.xml', __DIR__, 'default_default')
        ));

        $this->assertFileExists($config->getSchemaFile());
    }

    /**
     * @param string $themePath
     * @param mixed $expected
     * @dataProvider getThemeTitleDataProvider
     */
    public function testGetThemeTitle($themePath, $expected)
    {
        $config = new \Magento\Config\Theme(array(
            sprintf('%s/_files/area/%s/theme.xml', __DIR__, $themePath)
        ));
        $this->assertSame($expected, $config->getThemeTitle());
    }

    /**
     * @return array
     */
    public function getThemeTitleDataProvider()
    {
        return array(
            array('default_default', 'Default'),
            array('default_test',    'Test'),
        );
    }

    /**
     * @param string $themePath
     * @param mixed $expected
     * @dataProvider getParentThemeDataProvider
     */
    public function testGetParentTheme($themePath, $expected)
    {
        $config = new \Magento\Config\Theme(array(
            sprintf('%s/_files/area/%s/theme.xml', __DIR__, $themePath)
        ));
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
            array('test_external_package_descendant', array('default_test2')),
        );
    }
}
