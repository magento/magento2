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
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Test theme model
 */
class Mage_Core_Model_ThemeTest extends Magento_Test_TestCase_ObjectManagerAbstract
{
    /**
     * Test load from configuration
     *
     * @covers Mage_Core_Model_Theme::loadFromConfiguration
     */
    public function testLoadFromConfiguration()
    {
        $themePath = implode(DIRECTORY_SEPARATOR, array(__DIR__, '_files', 'theme', 'theme.xml'));

        /** @var $themeMock Mage_Core_Model_Theme */
        $arguments = $this->_getConstructArguments(self::MODEL_ENTITY);
        $themeMock = $this->getMock('Mage_Core_Model_Theme', array('_init'), $arguments, '', true);
        $themeMock->loadFromConfiguration($themePath);

        $this->assertEquals($this->_expectedThemeDataFromConfiguration(), $themeMock->getData());
    }

    /**
     * Test load invalid configuration
     *
     * @covers Mage_Core_Model_Theme::loadFromConfiguration
     * @expectedException Magento_Exception
     */
    public function testLoadInvalidConfiguration()
    {
        $themePath = implode(DIRECTORY_SEPARATOR, array(__DIR__, '_files', 'theme', 'theme_invalid.xml'));

        /** @var $themeMock Mage_Core_Model_Theme */
        $arguments = $this->_getConstructArguments(self::MODEL_ENTITY);
        $themeMock = $this->getMock('Mage_Core_Model_Theme', array('_init'), $arguments, '', true);
        $themeMock->loadFromConfiguration($themePath);

        $this->assertEquals($this->_expectedThemeDataFromConfiguration(), $themeMock->getData());
    }

    /**
     * Expected theme data from configuration
     *
     * @return array
     */
    public function _expectedThemeDataFromConfiguration()
    {
        return array(
            'theme_code'           => 'iphone',
            'theme_title'          => 'Iphone',
            'theme_version'        => '2.0.0.1',
            'parent_theme'         => null,
            'is_featured'          => true,
            'magento_version_from' => '2.0.0.1-dev1',
            'magento_version_to'   => '*',
            'theme_path'           => 'default/iphone',
            'preview_image'        => 'images/preview.png',
            'theme_directory'      => implode(DIRECTORY_SEPARATOR, array(__DIR__, '_files', 'theme'))
        );
    }
}
