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

class Mage_Core_Model_Config_OptionsTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Core_Model_Config_Options
     */
    protected $_model;

    /**
     * @var array
     */
    protected $_sourceData;

    /**
     * @var array
     */
    protected $_varDir;

    protected function setUp()
    {
        $ioModel = $this->getMock('Varien_Io_File', array('checkAndCreateFolder'));
        $this->_sourceData = array(
            'app_dir' => __DIR__ . DIRECTORY_SEPARATOR . 'app',
            'io' => $ioModel,
        );
        $this->_varDir = __DIR__ . DIRECTORY_SEPARATOR . 'var';
    }

    public function testGetVarDir()
    {
        $this->_sourceData['io']->expects($this->once())
            ->method('checkAndCreateFolder')
            ->with($this->equalTo($this->_varDir))
            ->will($this->returnValue(true));

        $this->_model = new Mage_Core_Model_Config_Options($this->_sourceData);
        $result = $this->_model->getVarDir();
        $this->assertEquals($this->_varDir, $result);
    }

    /**
     * @expectedException Mage_Core_Exception
     */
    public function testGetVarDirWithException()
    {
        $this->_sourceData['io']->expects($this->at(0))
            ->method('checkAndCreateFolder')
            ->with($this->equalTo($this->_varDir))
            ->will($this->throwException(new Exception));
        $this->_model = new Mage_Core_Model_Config_Options($this->_sourceData);
    }

    public function testCreateDirIfNotExists()
    {
        $checkDir = __DIR__ . DIRECTORY_SEPARATOR . 'test';
        $this->_sourceData['io']->expects($this->at(0))
            ->method('checkAndCreateFolder')
            ->with($this->equalTo($this->_varDir))
            ->will($this->returnValue(true));

        $this->_sourceData['io']->expects($this->at(1))
            ->method('checkAndCreateFolder')
            ->with($this->equalTo($checkDir))
            ->will($this->returnValue(true));

        $this->_model = new Mage_Core_Model_Config_Options($this->_sourceData);

        $result = $this->_model->createDirIfNotExists($checkDir);
        $this->assertEquals(true, $result);
    }

    public function testCreateDirIfNotExistsNegativeResult()
    {
        $checkDir = __DIR__ . DIRECTORY_SEPARATOR . 'dirNotExists';
        $this->_sourceData['io']->expects($this->at(0))
            ->method('checkAndCreateFolder')
            ->with($this->equalTo($this->_varDir))
            ->will($this->returnValue(true));

        $this->_sourceData['io']->expects($this->at(1))
            ->method('checkAndCreateFolder')
            ->will($this->throwException(new Exception));

        $this->_model = new Mage_Core_Model_Config_Options($this->_sourceData);
        $result = $this->_model->createDirIfNotExists($checkDir);
        $this->assertEquals(false, $result);
    }
}