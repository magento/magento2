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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_Install_Model_Installer_ConfigTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    protected static $_tmpDir = '';

    public static function setUpBeforeClass()
    {
        self::$_tmpDir = Mage::getBaseDir(Mage_Core_Model_Dir::VAR_DIR) . DIRECTORY_SEPARATOR . __CLASS__;
        mkdir(self::$_tmpDir);
    }

    public static function tearDownAfterClass()
    {
        Varien_Io_File::rmdirRecursive(self::$_tmpDir);
    }

    public function testInstall()
    {
        file_put_contents(self::$_tmpDir . '/local.xml.template', "test; {{date}}; {{base_url}}; {{unknown}}");
        $expectedFile = self::$_tmpDir . '/local.xml';

        $config = $this->getMock('Mage_Core_Model_Config', array('getDistroBaseUrl'), array(), '', false);
        $config->expects($this->once())->method('getDistroBaseUrl')->will($this->returnValue('http://example.com/'));
        $expectedContents = "test; <![CDATA[d-d-d-d-d]]>; <![CDATA[http://example.com/]]>; {{unknown}}";
        $dirs = new Mage_Core_Model_Dir(
            self::$_tmpDir, new Varien_Io_File(), array(), array(Mage_Core_Model_Dir::CONFIG => self::$_tmpDir)
        );

        $this->assertFileNotExists($expectedFile);
        $filesystem = new Magento_Filesystem(new Magento_Filesystem_Adapter_Local);
        $model = Mage::getModel('Mage_Install_Model_Installer_Config', array(
            'config' => $config, 'dirs' => $dirs, 'filesystem' => $filesystem
        ));
        $model->install();
        $this->assertFileExists($expectedFile);
        $this->assertStringEqualsFile($expectedFile, $expectedContents);
    }

    public function testGetFormData()
    {
        /** @var $model Mage_Install_Model_Installer_Config */
        $model = Mage::getModel('Mage_Install_Model_Installer_Config');
        /** @var $result Varien_Object */
        $result = $model->getFormData();
        $this->assertInstanceOf('Varien_Object', $result);
        $data = $result->getData();
        $this->assertArrayHasKey('db_host', $data);
    }
}
