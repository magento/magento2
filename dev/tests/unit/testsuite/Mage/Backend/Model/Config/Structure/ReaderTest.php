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
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
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

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_converterMock;

    public function setUp()
    {
        $this->_appConfigMock = $this->getMock('Mage_Core_Model_Config', array(), array(), '', false);
        $this->_cacheMock = $this->getMock('Mage_Core_Model_Cache', array(), array(), '', false);
        $this->_cacheMock->expects($this->any())->method('canUse')->will($this->returnValue(true));
        $this->_converterMock = $this->getMock(
            'Mage_Backend_Model_Config_Structure_Converter', array(), array(), '', false
        );
    }

    public function testGetConfigurationLoadsConfigFromCacheWhenCacheIsEnabled()
    {
        $cachedObject = new StdClass();
        $cachedObject->foo = 'bar';
        $cachedData = serialize($cachedObject);

        $this->_cacheMock->expects($this->once())->method('load')
            ->with(Mage_Backend_Model_Config_Structure_Reader::CACHE_SYSTEM_CONFIGURATION_STRUCTURE)
            ->will($this->returnValue($cachedData));

        $model = new Mage_Backend_Model_Config_Structure_Reader(
            $this->_appConfigMock, $this->_cacheMock, $this->_converterMock
        );
        $this->assertEquals($cachedObject, $model->getData());
    }

    public function testGetConfigurationLoadsConfigFromFilesAndCachesIt()
    {
        $expected = array('var' => 'val');
        $this->_cacheMock->expects($this->once())->method('load')->will($this->returnValue(false));

        $this->_converterMock->expects($this->once())->method('convert')->will($this->returnValue(
            array('config' => array('system' => $expected))
        ));
        $filePath = dirname(dirname(__DIR__)) . '/_files';
        $this->_appConfigMock->expects($this->once())
            ->method('getModuleConfigurationFiles')
            ->will($this->returnValue(array($filePath . '/system_2.xml')));

        $this->_cacheMock->expects($this->once())->method('save')->with(
            serialize($expected)
        );

        $model = new Mage_Backend_Model_Config_Structure_Reader(
            $this->_appConfigMock, $this->_cacheMock, $this->_converterMock, false
        );
        $this->assertEquals($expected, $model->getData());
    }
}
