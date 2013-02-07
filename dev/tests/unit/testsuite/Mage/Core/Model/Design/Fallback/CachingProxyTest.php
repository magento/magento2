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

class Mage_Core_Model_Design_Fallback_CachingProxyTest extends PHPUnit_Framework_TestCase
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
     * @var Mage_Core_Model_Design_Fallback_CachingProxy|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_model;

    /**
     * Mocked fallback object, with file resolution methods ready to be substituted.
     *
     * @var Mage_Core_Model_Design_Fallback|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_fallback;

    public function setUp()
    {
        $this->_tmpDir = TESTS_TEMP_DIR . DIRECTORY_SEPARATOR . 'fallback';
        mkdir($this->_tmpDir);
        $this->_fallback = $this->getMock(
            'Mage_Core_Model_Design_Fallback',
            array('getFile', 'getLocaleFile', 'getViewFile', 'getArea', 'getPackage', 'getTheme', 'getLocale'),
            array(),
            '',
            false
        );
        $this->_fallback->expects($this->any())->method('getArea')->will($this->returnValue('a'));
        $this->_fallback->expects($this->any())->method('getPackage')->will($this->returnValue('p'));
        $this->_fallback->expects($this->any())->method('getTheme')->will($this->returnValue('t'));
        $this->_fallback->expects($this->any())->method('getLocale')->will($this->returnValue('l'));
        $this->_model = new Mage_Core_Model_Design_Fallback_CachingProxy(
            $this->_fallback, $this->_createFilesystem(), $this->_tmpDir, __DIR__, true
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
        new Mage_Core_Model_Design_Fallback_CachingProxy($this->_fallback, $this->_createFilesystem(), $this->_tmpDir,
            __DIR__ . '/invalid_dir');
    }

    public function testDestruct()
    {
        $this->_fallback->expects($this->once())
            ->method('getFile')
            ->will($this->returnValue(__DIR__ . DIRECTORY_SEPARATOR . 'test.txt'));
        $suffix = uniqid();
        $model = new Mage_Core_Model_Design_Fallback_CachingProxy(
            $this->_fallback,
            $this->_createFilesystem(),
            $this->_tmpDir . DIRECTORY_SEPARATOR . $suffix,
            __DIR__,
            true
        );
        $expectedFile = $this->_tmpDir . DIRECTORY_SEPARATOR . $suffix . DIRECTORY_SEPARATOR . 'a_t_l.ser';
        $model->getFile('does not matter');
        $this->assertFileNotExists($expectedFile);
        unset($model);
        $this->assertFileExists($expectedFile);
        $contents = unserialize(file_get_contents($expectedFile));
        $this->assertContains('test.txt', $contents);
    }

    /**
     * @covers Mage_Core_Model_Design_Fallback_CachingProxy::getFile
     * @covers Mage_Core_Model_Design_Fallback_CachingProxy::getLocaleFile
     * @covers Mage_Core_Model_Design_Fallback_CachingProxy::getViewFile
     */
    public function testProxyMethods()
    {
        $fileArg = 'file.txt';
        $moduleArg = 'module';
        $path = __DIR__ . DIRECTORY_SEPARATOR;
        $this->_fallback->expects($this->once())
            ->method('getFile')->with($fileArg, $moduleArg)->will($this->returnValue("{$path}one"));
        $this->_fallback->expects($this->once())
            ->method('getLocaleFile')->with($fileArg)->will($this->returnValue("{$path}two"));
        $this->_fallback->expects($this->once())
            ->method('getViewFile')->with($fileArg, $moduleArg)->will($this->returnValue("{$path}three"));

        // Call each method twice to ensure the proxied method is called once
        $this->assertEquals("{$path}one", $this->_model->getFile($fileArg, $moduleArg));
        $this->assertEquals("{$path}one", $this->_model->getFile($fileArg, $moduleArg));
        $this->assertEquals("{$path}two", $this->_model->getLocaleFile($fileArg));
        $this->assertEquals("{$path}two", $this->_model->getLocaleFile($fileArg));
        $this->assertEquals("{$path}three", $this->_model->getViewFile($fileArg, $moduleArg));
        $this->assertEquals("{$path}three", $this->_model->getViewFile($fileArg, $moduleArg));
    }

    /**
     * Test that proxy caches published skin path, and further calls do not use fallback model
     */
    public function testNotifyViewFilePublished()
    {
        $moduleArg = '...';
        $fixture = __DIR__ . DIRECTORY_SEPARATOR . uniqid();
        $anotherFixture = __DIR__ . DIRECTORY_SEPARATOR . uniqid();

        $this->_fallback->expects($this->once())->method('getViewFile')->will($this->returnValue($fixture));
        $this->assertEquals($fixture, $this->_model->getViewFile('file.txt', $moduleArg));
        $this->assertSame(
            $this->_model, $this->_model->setFilePathToMap($anotherFixture, 'file.txt', $moduleArg)
        );
        $this->assertEquals($anotherFixture, $this->_model->getViewFile('file.txt', $moduleArg));
    }

    /**
     * @return Magento_Filesystem
     */
    protected function _createFilesystem()
    {
        return new Magento_Filesystem(new Magento_Filesystem_Adapter_Local());
    }
}
