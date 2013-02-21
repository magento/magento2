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
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Testing of interaction with the file system
 */
class Mage_Core_Model_DirFilesystemTest extends PHPUnit_Framework_TestCase
{
    /**
     * Temporary directory with readonly permissions
     *
     * @var string
     */
    protected static $_tmpReadonlyDir;

    /**
     * Temporary directory with write permissions
     *
     * @var string
     */
    protected static $_tmpWritableDir;

    public static function setUpBeforeClass()
    {
        $appInstallDir = Magento_Test_Helper_Bootstrap::getInstance()->getAppInstallDir();
        self::$_tmpReadonlyDir = $appInstallDir . DIRECTORY_SEPARATOR . __CLASS__ . '-readonly';
        self::$_tmpWritableDir = $appInstallDir . DIRECTORY_SEPARATOR . __CLASS__ . '-writable';

        foreach (array(self::$_tmpReadonlyDir => 0444, self::$_tmpWritableDir => 0777) as $tmpDir => $dirMode) {
            if (!is_dir($tmpDir)) {
                mkdir($tmpDir);
            }
            chmod($tmpDir, $dirMode);
        }
    }

    public static function tearDownAfterClass()
    {
        foreach (array(self::$_tmpReadonlyDir, self::$_tmpWritableDir) as $tmpDir) {
            Varien_Io_File::chmodRecursive($tmpDir, 0777);
            Varien_Io_File::rmdirRecursive($tmpDir);
        }
    }

    /**
     * Require the temporary fixture directory to be readonly, or skip the current test otherwise
     */
    protected function _requireReadonlyDir()
    {
        if (is_writable(self::$_tmpReadonlyDir)) {
            $this->markTestSkipped('Environment does not allow changing access permissions for files/directories.');
        }
    }

    /**
     * Instantiate and return the model passing a custom directory path
     *
     * @param string $dirCode
     * @param string $path
     * @return Mage_Core_Model_Dir
     */
    protected function _createModelWithCustomDir($dirCode, $path)
    {
        $filesystem = Mage::getObjectManager()->get('Magento_Filesystem');
        $appParams = Magento_Test_Helper_Bootstrap::getInstance()->getAppInitParams();
        $dirs = isset($appParams[Mage::PARAM_APP_DIRS]) ? $appParams[Mage::PARAM_APP_DIRS] : array();
        $dirs[$dirCode] = $path;
        return new Mage_Core_Model_Dir($filesystem, __DIR__, array(), $dirs);
    }

    /**
     * Setup expectation of the directory bootstrap exception
     *
     * @param string $path
     */
    protected function _expectDirBootstrapException($path)
    {
        $this->setExpectedException('Magento_BootstrapException', "Path '$path' has to be a writable directory.");
    }

    /**
     * @param string $dirCode
     * @dataProvider writableDirCodeDataProvider
     * @magentoAppIsolation enabled
     */
    public function testExistingReadonlyDir($dirCode)
    {
        $this->_requireReadonlyDir();
        $dirs = $this->_createModelWithCustomDir($dirCode, self::$_tmpReadonlyDir);
        // expectation is intentionally set up after the model creation to ensure validation is performed on demand
        $this->_expectDirBootstrapException(self::$_tmpReadonlyDir);
        $dirs->getDir($dirCode);
    }

    /**
     * @param string $dirCode
     * @dataProvider writableDirCodeDataProvider
     * @magentoAppIsolation enabled
     */
    public function testExistingFile($dirCode)
    {
        $dirs = $this->_createModelWithCustomDir($dirCode, __FILE__);
        $this->_expectDirBootstrapException(__FILE__);
        $dirs->getDir($dirCode);
    }

    /**
     * @param string $dirCode
     * @dataProvider writableDirCodeDataProvider
     * @magentoAppIsolation enabled
     */
    public function testNewDirInReadonlyDir($dirCode)
    {
        $this->_requireReadonlyDir();
        $path = self::$_tmpReadonlyDir . DIRECTORY_SEPARATOR . 'non_existing_dir';
        $dirs = $this->_createModelWithCustomDir($dirCode, $path);
        $this->_expectDirBootstrapException($path);
        $dirs->getDir($dirCode);
    }

    /**
     * @param string $dirCode
     * @dataProvider writableDirCodeDataProvider
     * @magentoAppIsolation enabled
     */
    public function testNewDirInWritableDir($dirCode)
    {
        $path = self::$_tmpWritableDir . DIRECTORY_SEPARATOR . $dirCode;
        $dirs = $this->_createModelWithCustomDir($dirCode, $path);
        $this->assertFileNotExists($path);
        $dirs->getDir($dirCode);
        $this->assertFileExists($path);
    }

    public function writableDirCodeDataProvider()
    {
        return array(
            Mage_Core_Model_Dir::MEDIA      => array(Mage_Core_Model_Dir::MEDIA),
            Mage_Core_Model_Dir::VAR_DIR    => array(Mage_Core_Model_Dir::VAR_DIR),
            Mage_Core_Model_Dir::TMP        => array(Mage_Core_Model_Dir::TMP),
            Mage_Core_Model_Dir::CACHE      => array(Mage_Core_Model_Dir::CACHE),
            Mage_Core_Model_Dir::LOG        => array(Mage_Core_Model_Dir::LOG),
            Mage_Core_Model_Dir::SESSION    => array(Mage_Core_Model_Dir::SESSION),
        );
    }
}
