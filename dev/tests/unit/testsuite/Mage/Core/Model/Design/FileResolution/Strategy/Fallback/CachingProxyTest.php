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

class Mage_Core_Model_Design_FileResolution_Strategy_Fallback_CachingProxyTest extends PHPUnit_Framework_TestCase
{
    /**
     * Temp directory for the model to store maps
     *
     * @var string
     */
    protected $_tmpDir;

    /**
     * Mock of the model to be tested. Operates the mocked fallback object.
     *
     * @var Mage_Core_Model_Design_FileResolution_Strategy_Fallback_CachingProxy|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_model;

    /**
     * Mocked fallback object, with file resolution methods ready to be substituted.
     *
     * @var Mage_Core_Model_Design_FileResolution_Strategy_Fallback|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_fallback;

    /**
     * Theme model, pre-created in setUp() for usage in tests
     *
     * @var Mage_Core_Model_Theme|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_themeModel;

    public function setUp()
    {
        $this->_tmpDir = TESTS_TEMP_DIR . DIRECTORY_SEPARATOR . 'fallback';
        mkdir($this->_tmpDir);

        $this->_fallback = $this->getMock(
            'Mage_Core_Model_Design_FileResolution_Strategy_Fallback',
            array(),
            array(),
            '',
            false
        );

        $this->_themeModel = PHPUnit_Framework_MockObject_Generator::getMock(
            'Mage_Core_Model_Theme',
            array(),
            array(),
            '',
            false,
            false
        );
        $this->_themeModel->expects($this->any())
            ->method('getId')
            ->will($this->returnValue('t'));

        $this->_model = new Mage_Core_Model_Design_FileResolution_Strategy_Fallback_CachingProxy(
            $this->_fallback, $this->_createFilesystem(), $this->_tmpDir, TESTS_TEMP_DIR, true
        );
    }

    protected function tearDown()
    {
        Varien_Io_File::rmdirRecursive($this->_tmpDir);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testConstructInvalidDir()
    {
        $this->_model = new Mage_Core_Model_Design_FileResolution_Strategy_Fallback_CachingProxy(
            $this->_fallback, $this->_createFilesystem(), $this->_tmpDir, TESTS_TEMP_DIR . '/invalid_dir'
        );
    }

    public function testDestruct()
    {
        $this->_fallback->expects($this->once())
            ->method('getFile')
            ->will($this->returnValue(TESTS_TEMP_DIR . DIRECTORY_SEPARATOR . 'test.txt'));

        $expectedFile = $this->_tmpDir . DIRECTORY_SEPARATOR . 'a_t_.ser';

        $this->_model->getFile('a', $this->_themeModel, 'does not matter', 'Some_Module');
        $this->assertFileNotExists($expectedFile);
        unset($this->_model);
        $this->assertFileExists($expectedFile);
        $contents = file_get_contents($expectedFile);
        $this->assertContains('test.txt', $contents);
        $this->assertContains('Some_Module', $contents);
    }

    public function testDestructNoMapSaved()
    {
        $this->_fallback->expects($this->once())
            ->method('getFile')
            ->will($this->returnValue(TESTS_TEMP_DIR . DIRECTORY_SEPARATOR . 'test.txt'));
        $model = new Mage_Core_Model_Design_FileResolution_Strategy_Fallback_CachingProxy(
            $this->_fallback, $this->_createFilesystem(), $this->_tmpDir, TESTS_TEMP_DIR, false
        );

        $unexpectedFile = $this->_tmpDir . DIRECTORY_SEPARATOR . 'a_t_.ser';

        $model->getFile('a', $this->_themeModel, 'does not matter', 'Some_Module');
        unset($model);
        $this->assertFileNotExists($unexpectedFile);
    }

    /**
     * @param string $method
     * @param array $params
     * @param string $expectedResult
     * @dataProvider proxyMethodsDataProvider
     * @covers Mage_Core_Model_Design_FileResolution_Strategy_Fallback_CachingProxy::getFile
     * @covers Mage_Core_Model_Design_FileResolution_Strategy_Fallback_CachingProxy::getLocaleFile
     * @covers Mage_Core_Model_Design_FileResolution_Strategy_Fallback_CachingProxy::getViewFile
     */
    public function testProxyMethods($method, $params, $expectedResult)
    {
        $helper = new Magento_Test_Helper_ProxyTesting();
        $actualResult = $helper->invokeWithExpectations($this->_model, $this->_fallback, $method, $params,
            $expectedResult);
        $this->assertEquals($expectedResult, $actualResult);
    }

    /**
     * @return array
     */
    public static function proxyMethodsDataProvider()
    {
        $themeModel = PHPUnit_Framework_MockObject_Generator::getMock(
            'Mage_Core_Model_Theme',
            array(),
            array(),
            '',
            false,
            false
        );

        return array(
            'getFile' => array(
                'getFile',
                array('area51', $themeModel, 'file.txt', 'Some_Module'),
                TESTS_TEMP_DIR . DIRECTORY_SEPARATOR . 'fallback' . DIRECTORY_SEPARATOR . 'file.txt',
            ),
            'getLocaleFile' => array(
                'getLocaleFile',
                array('area51', $themeModel, 'sq_AL', 'file.txt'),
                'path/to/locale_file.txt',
            ),
            'getViewFile' => array(
                'getViewFile',
                array('area51', $themeModel, 'uk_UA', 'file.txt', 'Some_Module'),
                'path/to/view_file.txt',
            ),
        );
    }

    public function testSetViewFilePathToMap()
    {
        $materializedFilePath = TESTS_TEMP_DIR . DIRECTORY_SEPARATOR . 'path' . DIRECTORY_SEPARATOR . 'file.txt';

        $result = $this->_model->setViewFilePathToMap('area51', $this->_themeModel, 'en_US', 'Some_Module', 'file.txt',
            $materializedFilePath);
        $this->assertEquals($this->_model, $result);

        $this->_fallback->expects($this->never())
            ->method('getViewFile');
        $result = $this->_model->getViewFile('area51', $this->_themeModel, 'en_US', 'file.txt', 'Some_Module');
        $this->assertEquals($materializedFilePath, $result);
    }

    /**
     * @return Magento_Filesystem
     */
    protected function _createFilesystem()
    {
        return new Magento_Filesystem(new Magento_Filesystem_Adapter_Local());
    }
}
