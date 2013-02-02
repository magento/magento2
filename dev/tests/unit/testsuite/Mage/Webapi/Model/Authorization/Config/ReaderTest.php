<?php
/**
 * Test class for Mage_Webapi_Model_Authorization_Config_Reader
 *
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
class Mage_Webapi_Model_Authorization_Config_ReaderTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Webapi_Model_Authorization_Config_Reader
     */
    protected $_reader;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject|Mage_Core_Model_Config
     */
    protected $_configMock;

    /**
     * Initialize reader instance
     */
    protected function setUp()
    {
        $path = array(__DIR__, '..', '..', '_files', 'acl.xml');
        $path = realpath(implode(DIRECTORY_SEPARATOR, $path));
        $this->_configMock = $this->getMock('Mage_Core_Model_Config', array(), array(), '', false);
        $this->_configMock->expects($this->any())
            ->method('getModuleDir')
            ->with('etc', 'Mage_Webapi')
            ->will($this->returnValue(
                realpath(__DIR__ . '/../../../../../../../../../app/code/core/Mage/Webapi/etc'))
        );

        $this->_reader = new Mage_Webapi_Model_Authorization_Config_Reader($this->_configMock, array($path));
    }

    /**
     * Unset reader instance.
     */
    protected function tearDown()
    {
        unset($this->_reader);
        unset($this->_configMock);
    }

    /**
     * Check that correct XSD file is provided.
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
