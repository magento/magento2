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
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * @group integrity
 */
class Integrity_Theme_XmlFilesTest extends PHPUnit_Framework_TestCase
{
    /**
     * @param string $file
     * @dataProvider viewConfigFileDataProvider
     */
    public function testViewConfigFile($file)
    {
        $this->_validateConfigFile($file, Mage::getBaseDir('lib') . '/Magento/Config/view.xsd');
    }

    /**
     * @return array
     */
    public function viewConfigFileDataProvider()
    {
        $result = array();
        foreach (glob(Mage::getRoot() . '/design/*/*/*/view.xml') as $file) {
            $result[] = array($file);
        }
        return $result;
    }

    /**
     * @param string $themeDir
     * @dataProvider themeConfigFileExistsDataProvider
     */
    public function testThemeConfigFileExists($themeDir)
    {
        $this->assertFileExists($themeDir . '/theme.xml');
    }

    /**
     * @return array
     */
    public function themeConfigFileExistsDataProvider()
    {
        $result = array();
        foreach (glob(Mage::getRoot() . '/design/*/*/*', GLOB_ONLYDIR) as $themeDir) {
            $result[] = array($themeDir);
        }
        return $result;
    }

    /**
     * @param string $file
     * @dataProvider themeConfigFileDataProvider
     */
    public function testThemeConfigFile($file)
    {
        $this->_validateConfigFile($file, Mage::getBaseDir('lib') . '/Magento/Config/theme.xsd');
    }

    /**
     * @return array
     */
    public function themeConfigFileDataProvider()
    {
        $result = array();
        foreach (glob(Mage::getRoot() . '/design/*/*/*/theme.xml') as $file) {
            $result[] = array($file);
        }
        return $result;
    }

    /**
     * Perform test whether a configuration file is valid
     *
     * @param string $file
     * @param string $schemaFile
     * @throws PHPUnit_Framework_AssertionFailedError if file is invalid
     */
    protected function _validateConfigFile($file, $schemaFile)
    {
        $domConfig = new Magento_Config_Dom(file_get_contents($file));
        $result = $domConfig->validate($schemaFile, $errors);
        $message = "Invalid XML-file: {$file}\n";
        foreach ($errors as $error) {
            $message .= "{$error->message} Line: {$error->line}\n";
        }
        $this->assertTrue($result, $message);
    }
}
