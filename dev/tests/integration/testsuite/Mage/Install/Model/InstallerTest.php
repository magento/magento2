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
 * @package     Mage_Install
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Install_Model_InstallerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    protected static $_tmpDir = '';

    /**
     * @var string
     */
    protected static $_tmpConfigFile = '';

    /**
     * @var Mage_Install_Model_Installer
     */
    protected $_model;

    public static function setUpBeforeClass()
    {
        self::$_tmpDir = Mage::getBaseDir(Mage_Core_Model_Dir::VAR_DIR) . DIRECTORY_SEPARATOR . __CLASS__;
        self::$_tmpConfigFile = self::$_tmpDir . DIRECTORY_SEPARATOR . 'local.xml';
        mkdir(self::$_tmpDir);
    }

    public static function tearDownAfterClass()
    {
        Varien_Io_File::rmdirRecursive(self::$_tmpDir);
    }

    protected function setUp()
    {
        $this->_model = Mage::getModel('Mage_Install_Model_Installer');
    }

    protected function tearDown()
    {
        $this->_model = null;
    }

    /**
     * Emulate configuration directory for the installer config model.
     * Method usage should be accompanied with '@magentoAppIsolation enabled' because of the object manager pollution.
     *
     * @param string $dir
     */
    protected function _emulateInstallerConfigDir($dir)
    {
        $objectManager = Mage::getObjectManager();
        $filesystem = new Magento_Filesystem(new Magento_Filesystem_Adapter_Local());
        $installerConfig = new Mage_Install_Model_Installer_Config(
            $objectManager->get('Mage_Core_Model_Config'),
            new Mage_Core_Model_Dir($filesystem, __DIR__, array(), array(Mage_Core_Model_Dir::CONFIG => $dir)),
            $objectManager->get('Mage_Core_Model_Config_Resource'),
            $filesystem
        );
        $objectManager->addSharedInstance($installerConfig, 'Mage_Install_Model_Installer_Config');
    }

    /**
     * @magentoDbIsolation enabled
     */
    public function testCreateAdministrator()
    {
        $userName = 'installer_test';
        $userPassword = '123123q';
        $userData = array(
            'username'  => $userName,
            'firstname' => 'First Name',
            'lastname'  => 'Last Name',
            'email'     => 'installer_test@example.com',
        );

        /** @var $user Mage_User_Model_User */
        $user = Mage::getModel('Mage_User_Model_User');
        $user->loadByUsername($userName);
        $this->assertEmpty($user->getId());

        $this->_model->createAdministrator($userData + array('password' => $userPassword));

        $user->loadByUsername($userName);
        $this->assertNotEmpty($user->getId());
        $this->assertEquals($userData, array_intersect_assoc($user->getData(), $userData));
        $this->assertNotEmpty($user->getPassword(), 'Password hash is expected to be loaded.');
        $this->assertNotEquals(
            $userPassword, $user->getPassword(),
            'Original password should not be stored/loaded as is for security reasons.'
        );
        $this->assertInstanceOf('Mage_User_Model_Role', $user->getRole());
        $this->assertEquals(1, $user->getRole()->getId(), 'User has to have admin privileges.');
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testInstallEncryptionKey()
    {
        $this->_emulateInstallerConfigDir(self::$_tmpDir);

        $keyPlaceholder = Mage_Install_Model_Installer_Config::TMP_ENCRYPT_KEY_VALUE;
        $fixtureConfigData = "<key>$keyPlaceholder</key>";
        $expectedConfigData = '<key>d41d8cd98f00b204e9800998ecf8427e</key>';

        file_put_contents(self::$_tmpConfigFile, $fixtureConfigData);
        $this->assertEquals($fixtureConfigData, file_get_contents(self::$_tmpConfigFile));

        $this->_model->installEncryptionKey('d41d8cd98f00b204e9800998ecf8427e');
        $this->assertEquals($expectedConfigData, file_get_contents(self::$_tmpConfigFile));
    }

    /**
     * @magentoAppIsolation enabled
     * @expectedException Magento_Exception
     * @expectedExceptionMessage Key must not exceed
     */
    public function testInstallEncryptionKeySizeViolation()
    {
        // isolate the application from the configuration pollution, if the test fails
        $this->_emulateInstallerConfigDir(self::$_tmpDir);

        $this->_model->installEncryptionKey(str_repeat('a', 57));
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testGetValidEncryptionKey()
    {
        $validKey = 'abcdef1234567890';
        $this->assertEquals($validKey, $this->_model->getValidEncryptionKey($validKey));
    }

    /**
     * @magentoAppIsolation enabled
     * @expectedException Magento_Exception
     * @expectedExceptionMessage Key must not exceed
     */
    public function testGetValidEncryptionKeySizeViolation()
    {
        $this->_model->getValidEncryptionKey(str_repeat('1', 57));
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testGetValidEncryptionKeyRandom()
    {
        $actualKey = $this->_model->getValidEncryptionKey();
        $this->assertRegExp('/^[a-f0-9]{32}$/', $actualKey);
        $this->assertNotEquals($actualKey, $this->_model->getValidEncryptionKey());
    }
}
