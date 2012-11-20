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
 * @package     Mage_Backend
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Backend_Model_Config_Structure_ReaderTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Backend_Model_Config_Structure_Reader
     */
    protected $_model;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_appConfigMock;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_cacheMock;

    public function setUp()
    {
        $this->_appConfigMock = $this->getMock('Mage_Core_Model_Config', array(), array(), '', false);
        $this->_cacheMock = $this->getMock('Mage_Core_Model_Cache', array(), array(), '', false);
        $this->_cacheMock->expects($this->any())->method('canUse')->will($this->returnValue(true));

        $this->_model = new Mage_Backend_Model_Config_Structure_Reader(array(
            'config' => $this->_appConfigMock,
            'cache' => $this->_cacheMock
        ));
    }

    public function testGetConfigurationLoadsConfigFromCacheWhenCacheIsEnabled()
    {
        $cachedObject = new StdClass();
        $cachedObject->foo = 'bar';
        $cachedData = serialize($cachedObject);

        $this->_cacheMock->expects($this->once())->method('load')
            ->with(Mage_Backend_Model_Config_Structure_Reader::CACHE_SYSTEM_CONFIGURATION_STRUCTURE)
            ->will($this->returnValue($cachedData));

        $this->assertEquals($cachedObject, $this->_model->getConfiguration());
    }

    public function testGetConfigurationLoadsConfigFromFilesAndCachesIt()
    {
        $this->_cacheMock->expects($this->once())->method('load')->will($this->returnValue(false));

        $testFiles = array('file1', 'file2');

        $this->_appConfigMock->expects($this->once())
            ->method('getModuleConfigurationFiles')
            ->will($this->returnValue($testFiles));

        $configMock = new StdClass();
        $configMock->foo = "bar";

        $this->_appConfigMock->expects($this->once())
            ->method('getModelInstance')
            ->with('Mage_Backend_Model_Config_Structure', array('sourceFiles' => $testFiles))
            ->will($this->returnValue($configMock));

        $this->_cacheMock->expects($this->once())->method('save')->with(
            $this->isType('string')
        );

        $this->assertEquals($configMock, $this->_model->getConfiguration());
    }
}
