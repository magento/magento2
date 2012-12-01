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
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Integrity_Modular_AclConfigFilesTest extends PHPUnit_Framework_TestCase
{
    /**
     * Configuration acl file list
     *
     * @var array
     */
    protected $_fileList = array();

    /**
     * Path to scheme file
     *
     * @var string
     */
    protected $_schemeFile;

    public function setUp()
    {
        $readerMock = $this->getMock('Magento_Acl_Config_Reader', array('getShemaFile'), array(), '', false);
        $this->_schemeFile = $readerMock->getSchemaFile();
        $this->_prepareFileList();
    }

    /**
     * Prepare file list of ACL resources
     *
     * @return void
     */
    protected function _prepareFileList()
    {
        if (empty($this->_fileList)) {
            $this->_fileList = glob(Mage::getBaseDir('app') . '/*/*/*/*/etc/adminhtml/acl.xml');
        }
    }

    /**
     * Test each acl configuration file
     * @param string $file
     * @dataProvider aclConfigFileDataProvider
     */
    public function testAclConfigFile($file)
    {
        $domConfig = new Magento_Config_Dom(file_get_contents($file));
        $result = $domConfig->validate($this->_schemeFile, $errors);
        $message = "Invalid XML-file: {$file}\n";
        foreach ($errors as $error) {
            $message .= "{$error->message} Line: {$error->line}\n";
        }
        $this->assertTrue($result, $message);
    }

    /**
     * @return array
     */
    public function aclConfigFileDataProvider()
    {
        $this->_prepareFileList();
        $dataProviderResult = array();
        foreach ($this->_fileList as $file) {
            $dataProviderResult[$file] = array($file);
        }
        return $dataProviderResult;
    }

    /**
     * Test merged ACL configuration
     */
    public function testMergedConfiguration()
    {
        /** @var $dom Magento_Acl_Config_Reader **/
        $dom = Mage::getModel('Magento_Acl_Config_Reader', array('configFiles' => $this->_fileList))
            ->getAclResources();

        $domConfig = new Magento_Acl_Config_Reader_Dom($dom->saveXML());
        $errors = array();
        $result = $domConfig->validate($this->_schemeFile, $errors);
        $message = "Invalid merged ACL config\n";
        foreach ($errors as $error) {
            $message .= "{$error->message} Line: {$error->line}\n";
        }
        $this->assertTrue($result, $message);
    }
}
