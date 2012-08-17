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
 * @package     Magento
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Magento_Test_EnvironmentTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    protected static $_tmpDir;

    /**
     * @var Magento_Test_Environment
     */
    protected $_environment;

    /**
     * Calculate directories
     */
    public static function setUpBeforeClass()
    {
        self::$_tmpDir = realpath(dirname(__FILE__) . '/../../../../../../tmp');
    }

    protected function setUp()
    {
        $this->_environment = new Magento_Test_Environment(self::$_tmpDir);
    }

    public function testSetGetInstance()
    {
        Magento_Test_Environment::setInstance($this->_environment);
        $this->assertSame($this->_environment, Magento_Test_Environment::getInstance());
    }

    /**
     * @expectedException Magento_Exception
     */
    public function testGetInstance()
    {
        Magento_Test_Environment::setInstance(null);
        Magento_Test_Environment::getInstance();
    }

    /**
     * @depends testGetInstance
     * @expectedException Magento_Exception
     */
    public function testSetInstanceWithNull()
    {
        Magento_Test_Environment::setInstance(null);
        $instance = Magento_Test_Environment::getInstance();
    }

    /**
     * @expectedException Magento_Exception
     */
    public function testSetInstanceThrowExceptionIfParamNotValid()
    {
        Magento_Test_Environment::setInstance(new stdClass());
    }

    public function testGetTmpDir()
    {
        $this->assertEquals(self::$_tmpDir, $this->_environment->getTmpDir());
    }

    public function testCleanTmpDir()
    {
        $fileName = self::$_tmpDir . '/file.tmp';
        touch($fileName);

        try {
            $this->_environment->cleanTmpDir();
            $this->assertFileNotExists($fileName);
        } catch (Exception $e) {
            unlink($fileName);
            throw $e;
        }
    }

    public function testCleanDir()
    {
        $dir = self::$_tmpDir . '/subtmp';
        mkdir($dir, 0777);
        $fileName = $dir . '/file.tmp';
        touch($fileName);

        try {
            $this->_environment->cleanDir(self::$_tmpDir);
            $this->assertFalse(is_dir($dir));
        } catch (Exception $e) {
            if (file_exists($fileName)) {
                unlink($fileName);
            }
            rmdir($dir);
            throw $e;
        }
    }

    /**
     *
     */
    public function tearDown()
    {
        Magento_Test_Environment::setInstance($this->_environment);
    }
}
