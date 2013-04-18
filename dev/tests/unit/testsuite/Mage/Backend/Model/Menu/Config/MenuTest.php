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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Test class for Mage_Backend_Model_Url
 */
class Mage_Backend_Model_Config_MenuTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var PHPUnit_Framework_MockObject_MockObject|Mage_Core_Model_Config
     */
    protected $_configMock;

    protected function setUp()
    {
        $this->_configMock = $this->getMock('Mage_Core_Model_Config', array(), array(), '', false);
        $this->_configMock->expects($this->any())
            ->method('getModuleDir')
            ->with('etc', 'Mage_Backend')
            ->will(
                $this->returnValue(
                    realpath(__DIR__ . '/../../../../../../../../../app/code/Mage/Backend/etc')
                )
            );
    }

    protected function tearDown()
    {
        unset($this->_configMock);
    }

    /**
     * Test existence of xsd file
     */
    public function testGetSchemaFile()
    {
        $basePath = realpath(__DIR__)  . '/../../_files/';
        $files = array(
            $basePath . 'menu_1.xml',
        );
        $model = new Mage_Backend_Model_Menu_Config_Menu($this->_configMock, $files);
        $actual = $model->getSchemaFile();
        $this->assertFileExists($actual, 'XSD file [' . $actual . '] not exist');
    }

    /**
     * Test output data type of method getMergedConfig
     */
    public function testGetMergedConfigDataType()
    {
        $model = $this->getMockForAbstractClass(
            'Mage_Backend_Model_Menu_Config_Menu',
            array(),
            '',
            false
        );
        $this->assertInstanceOf('DOMDocument', $model->getMergedConfig(), 'Invalid output type');
    }

    /**
     * Test output data type of method getMergedConfig
     */
    public function testGetMergedConfig()
    {
        $basePath = realpath(__DIR__)  . '/../../_files/';

        $expectedFile = $basePath . 'menu_merged.xml';
        $files = array(
            $basePath . 'menu_1.xml',
            $basePath . 'menu_2.xml',
        );
        $model = new Mage_Backend_Model_Menu_Config_Menu($this->_configMock, $files);
        $actual = $model->getMergedConfig();
        $actual->preserveWhiteSpace = false;

        $this->assertInstanceOf('DOMDocument', $actual, 'Invalid output type');
        $expected = new DOMDocument();
        $expected->preserveWhiteSpace = false;
        $expected->load($expectedFile);
        $this->assertEqualXMLStructure(
            $expected->documentElement,
            $actual->documentElement,
            true,
            'Incorrect document structure'
        );
        $this->assertEquals($expected, $actual, 'Incorrect configuration merge');
    }

    /**
     * Test validation of invalid files
     * @expectedException Magento_Exception
     */
    public function testValidateInvalidConfig()
    {
        $basePath = realpath(__DIR__)  . '/../_files/';
        $files = array(
            $basePath . 'menu_1.xml',
            $basePath . 'menu_1.xml',
        );
        $model = new Mage_Backend_Model_Menu_Config_Menu($this->_configMock, $files);
        $model->validate();
    }

    /**
     * Test validation of valid files
     */
    public function testValidateValidConfig()
    {
        $basePath = realpath(__DIR__)  . '/../../_files/';
        $files = array(
            $basePath . 'menu_1.xml',
            $basePath . 'menu_2.xml',
        );
        $model = new Mage_Backend_Model_Menu_Config_Menu($this->_configMock, $files);
        try {
            $this->assertInstanceOf('Mage_Backend_Model_Menu_Config_Menu', $model->validate());
        } catch (Magento_Exception $e) {
            $this->fail($e->getMessage());
        }
    }
}
