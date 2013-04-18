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
 * @package     Mage_Webhook
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Test class for Mage_Webhook_Model_Authorization_Config_Reader
 */
class Mage_Webhook_Model_Authorization_Config_ReaderTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Webhook_Model_Authorization_Config_Reader
     */
    protected $_reader;

    /**
     * Initialize reader instance
     */
    protected function setUp()
    {
        $path = array(__DIR__, '..', '..', '_files', 'acl.xml');
        $path = realpath(implode(DIRECTORY_SEPARATOR, $path));
        $this->_reader = new Mage_Webhook_Model_Authorization_Config_Reader(array($path));
    }

    /**
     * Unset reader instance
     */
    protected function tearDown()
    {
        unset($this->_reader);
    }

    /**
     * Check that correct xsd file is provided
     */
    public function testGetSchemaFile()
    {
        $xsdPath = array(__DIR__, '..', '..', '_files', 'acl.xsd');
        $xsdPath = realpath(implode(DIRECTORY_SEPARATOR, $xsdPath));
        $actualXsdPath = $this->_reader->getSchemaFile();

        $this->assertInternalType('string', $actualXsdPath);
        $this->assertFileExists($actualXsdPath);
        $this->assertXmlFileEqualsXmlFile($xsdPath, $actualXsdPath);
    }
}
