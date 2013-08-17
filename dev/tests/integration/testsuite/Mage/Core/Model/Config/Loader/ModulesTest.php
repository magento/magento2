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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_Core_Model_Config_Loader_ModulesTest extends PHPUnit_Framework_TestCase
{
    /**
     * @param string $path
     */
    protected function _loadModule($path)
    {
        $dir = new Mage_Core_Model_Dir(
            __DIR__ . $path,
            array(),
            array(Mage_Core_Model_Dir::MODULES => __DIR__ . $path)
        );
        $loader = new Mage_Core_Model_Config_Loader_Modules(
            $this->getMock('Mage_Core_Model_Config_Primary', array(), array(), '', false),
            $dir,
            $this->getMock('Mage_Core_Model_Config_BaseFactory', array(), array(), '', false),
            $this->getMock('Mage_Core_Model_Config_Resource', array(), array(), '', false),
            $this->getMock('Mage_Core_Model_Config_Loader_Modules_File', array(), array(), '', false),
            $this->getMock('Magento_ObjectManager', array(), array(), '', false),
            $this->getMock('Mage_Core_Model_Config_Modules_SortedFactory', array(), array(), '', false),
            $this->getMock('Mage_Core_Model_Config_Loader_Local', array(), array(), '', false)
        );
        $config = new Mage_Core_Model_Config_Base('<config><modules/><global><di/></global></config>');
        $loader->load($config);
    }

    /**
     * @expectedException Magento_Exception
     * @expectedExceptionMessage The module 'Mage_Core' cannot be enabled without PHP extension 'fixture'
     */
    public function testLoadMissingExtension()
    {
        $this->_loadModule('/_files/single');
    }

    /**
     * @expectedException Magento_Exception
     * @expectedExceptionMessage The module 'Mage_Core' cannot be enabled. One of PHP extensions: 'version - v.1'
     */
    public function testLoadMissingExtensions()
    {
        $this->_loadModule('/_files/any');
    }
}
