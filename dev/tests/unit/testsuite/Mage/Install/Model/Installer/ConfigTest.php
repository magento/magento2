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
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Install_Model_Installer_ConfigTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    protected static $_tmpConfigFile = '';

    /**
     * @var Mage_Install_Model_Installer_Config
     */
    protected $_model;

    public static function setUpBeforeClass()
    {
        self::$_tmpConfigFile = TESTS_TEMP_DIR . DIRECTORY_SEPARATOR . 'local.xml';
    }

    public static function tearDownAfterClass()
    {
        if (file_exists(self::$_tmpConfigFile)) {
            unlink(self::$_tmpConfigFile);
        }
    }

    protected function setUp()
    {
        $filesystemHelper = new Magento_Test_Helper_FileSystem($this);
        $this->_model = new Mage_Install_Model_Installer_Config(
            $this->getMock('Mage_Core_Model_Config', array(), array(), '', false),
            $filesystemHelper->createDirInstance(
                __DIR__, array(), array(Mage_Core_Model_Dir::CONFIG => TESTS_TEMP_DIR)
            ),
            $this->getMock('Mage_Core_Model_Config_Resource', array(), array(), '', false),
            new Magento_Filesystem(new Magento_Filesystem_Adapter_Local())
        );
    }

    protected function tearDown()
    {
        $this->_model = null;
    }

    public function testReplaceTmpInstallDate()
    {
        $datePlaceholder = Mage_Install_Model_Installer_Config::TMP_INSTALL_DATE_VALUE;
        $fixtureConfigData = "<date>$datePlaceholder</date>";
        $expectedConfigData = '<date>Sat, 19 Jan 2013 18:50:39 -0800</date>';

        file_put_contents(self::$_tmpConfigFile, $fixtureConfigData);
        $this->assertEquals($fixtureConfigData, file_get_contents(self::$_tmpConfigFile));

        $this->_model->replaceTmpInstallDate('Sat, 19 Jan 2013 18:50:39 -0800');
        $this->assertEquals($expectedConfigData, file_get_contents(self::$_tmpConfigFile));
    }

    public function testReplaceTmpEncryptKey()
    {
        $keyPlaceholder = Mage_Install_Model_Installer_Config::TMP_ENCRYPT_KEY_VALUE;
        $fixtureConfigData = "<key>$keyPlaceholder</key>";
        $expectedConfigData = '<key>3c7cf2e909fd5e2268a6e1539ae3c835</key>';

        file_put_contents(self::$_tmpConfigFile, $fixtureConfigData);
        $this->assertEquals($fixtureConfigData, file_get_contents(self::$_tmpConfigFile));

        $this->_model->replaceTmpEncryptKey('3c7cf2e909fd5e2268a6e1539ae3c835');
        $this->assertEquals($expectedConfigData, file_get_contents(self::$_tmpConfigFile));
    }
}
