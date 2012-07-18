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

class Mage_Core_Model_Design_Fallback_CachingProxyTest extends PHPUnit_Framework_TestCase
{
    /**
     * Temp directory for the model to store maps
     *
     * @var string
     */
    protected static $_tmpDir;

    /**
     * Base dir as passed to the model
     *
     * @var string
     */
    protected $_baseDir;

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

    public static function setUpBeforeClass()
    {
        self::$_tmpDir = Magento_Test_Environment::getInstance()->getTmpDir() . DIRECTORY_SEPARATOR . 'fallback';
        if (!is_dir(self::$_tmpDir)) {
            mkdir(self::$_tmpDir);
        } else {
            Magento_Test_Environment::getInstance()->cleanDir(self::$_tmpDir);
        }
    }

    public static function tearDownAfterClass()
    {
        Magento_Test_Environment::getInstance()->cleanDir(self::$_tmpDir);
        rmdir(self::$_tmpDir);
    }

    public function setUp()
    {
        $this->_baseDir = DIRECTORY_SEPARATOR . 'base' . DIRECTORY_SEPARATOR . 'dir';
        $params = array(
            'area' => 'frontend',
            'package' => 'package',
            'theme' => 'theme',
            'skin' => 'skin',
            'locale' => 'en_US',
            'appConfig' => false,
            'themeConfig' => false,
            'canSaveMap' => false,
            'mapDir' => self::$_tmpDir,
            'baseDir' => $this->_baseDir
        );

        $this->_fallback = $this->getMock(
            'Mage_Core_Model_Design_Fallback',
            array('getFile', 'getLocaleFile', 'getSkinFile'),
            array($params)
        );

        $this->_model = $this->getMock(
            'Mage_Core_Model_Design_Fallback_CachingProxy',
            array('_getFallback'),
            array($params)
        );
        $this->_model->expects($this->any())
            ->method('_getFallback')
            ->will($this->returnValue($this->_fallback));
    }

    public function tearDown()
    {
        Magento_Test_Environment::getInstance()->cleanDir(self::$_tmpDir);
    }

    /**
     * Calls are repeated twice to verify, that fallback is used only once, and next time a proper value is returned
     * via cached map.
     */
    public function testGetFile()
    {
        $module = 'Some_Module';
        $expected = $this->_baseDir . DIRECTORY_SEPARATOR . 'path' . DIRECTORY_SEPARATOR . 'theme_file.ext';
        $expected = str_replace('/', DIRECTORY_SEPARATOR, $expected);
        $this->_fallback->expects($this->once())
            ->method('getFile')
            ->with('file.ext', $module)
            ->will($this->returnValue($expected));

        $actual = $this->_model->getFile('file.ext', $module);
        $this->assertEquals($expected, $actual);
        $actual = $this->_model->getFile('file.ext', $module);
        $this->assertEquals($expected, $actual);
    }

    /**
     * Calls are repeated twice to verify, that fallback is used only once, and next time a proper value is returned
     * via cached map.
     */
    public function testGetLocaleFile()
    {
        $expected = $this->_baseDir . DIRECTORY_SEPARATOR . 'path' . DIRECTORY_SEPARATOR . 'locale_file.ext';
        $this->_fallback->expects($this->once())
            ->method('getLocaleFile')
            ->with('file.ext')
            ->will($this->returnValue($expected));

        $actual = $this->_model->getLocaleFile('file.ext');
        $this->assertEquals($expected, $actual);
        $actual = $this->_model->getLocaleFile('file.ext');
        $this->assertEquals($expected, $actual);
    }

    /**
     * Calls are repeated twice to verify, that fallback is used only once, and next time a proper value is returned
     * via cached map.
     */
    public function testGetSkinFile()
    {
        $module = 'Some_Module';
        $expected = $this->_baseDir . DIRECTORY_SEPARATOR . 'path' . DIRECTORY_SEPARATOR . 'skin_file.ext';
        $this->_fallback->expects($this->once())
            ->method('getSkinFile')
            ->with('file.ext', $module)
            ->will($this->returnValue($expected));

        $actual = $this->_model->getSkinFile('file.ext', $module);
        $this->assertEquals($expected, $actual);
        $actual = $this->_model->getSkinFile('file.ext', $module);
        $this->assertEquals($expected, $actual);
    }

    /**
     * Test that proxy caches published skin path, and further calls do not use fallback model
     */
    public function testNotifySkinFilePublished()
    {
        $module = 'Some_Module';
        $file = $this->_baseDir . DIRECTORY_SEPARATOR . 'path' . DIRECTORY_SEPARATOR . 'file.ext';

        $this->_fallback->expects($this->once())
            ->method('getSkinFile')
            ->with($file, $module)
            ->will($this->returnValue(null));

        // Empty at first
        $this->assertNull($this->_model->getSkinFile($file, $module));

        // Store something
        $publicFilePath = $this->_baseDir . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'file.ext';
        $result = $this->_model->notifySkinFilePublished($publicFilePath, $file, $module);
        $this->assertSame($this->_model, $result);

        // Stored successfully
        $storedFilePath = $this->_model->getSkinFile($file, $module);
        $this->assertEquals($publicFilePath, $storedFilePath);
    }

    /**
     * Tests that proxy saves data between instantiations
     */
    public function testSaving()
    {
        $module = 'Some_Module';
        $file = 'internal/path/to/skin_file.ext';
        $expectedPublicFile = 'public/path/to/skin_file.ext';

        $params = array(
            'area' => 'frontend',
            'package' => 'package',
            'theme' => 'theme',
            'skin' => 'skin',
            'locale' => 'en_US',
            'canSaveMap' => true,
            'mapDir' => self::$_tmpDir,
            'baseDir' => ''
        );
        $model = new Mage_Core_Model_Design_Fallback_CachingProxy($params);
        $model->notifySkinFilePublished($expectedPublicFile, $file, $module);

        $globPath = self::$_tmpDir . DIRECTORY_SEPARATOR . '*.*';
        $this->assertEmpty(glob($globPath));
        unset($model);
        $this->assertNotEmpty(glob($globPath));

        /** @var $model Mage_Core_Model_Design_Fallback_CachingProxy */
        $model = $this->getMock(
            'Mage_Core_Model_Design_Fallback_CachingProxy',
            array('_getFallback'),
            array($params)
        );
        $model->expects($this->never())
            ->method('_getFallback');

        $actualPublicFile = $model->getSkinFile($file, $module);
        $this->assertEquals($expectedPublicFile, $actualPublicFile);
    }
}
