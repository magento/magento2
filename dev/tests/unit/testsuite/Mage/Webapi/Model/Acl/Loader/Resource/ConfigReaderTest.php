<?php
/**
 * Test class for Mage_Webapi_Model_Acl_Loader_Resource_ConfigReader
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
class Mage_Webapi_Model_Acl_Loader_Resource_ConfigReaderTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Webapi_Model_Acl_Loader_Resource_ConfigReader
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
        $path = array(__DIR__, '..', '..', '..', '_files', 'acl.xml');
        $path = realpath(implode(DIRECTORY_SEPARATOR, $path));
        $dirPath = array(
            __DIR__, '..', '..', '..', '..', '..', '..', '..', '..', '..', '..', 'app', 'code', 'Mage', 'Webapi', 'etc'
        );
        $dirPath = realpath(implode(DIRECTORY_SEPARATOR, $dirPath));
        $fileListMock = $this->getMockBuilder('Mage_Webapi_Model_Acl_Loader_Resource_ConfigReader_FileList')
            ->disableOriginalConstructor()
            ->getMock();
        $fileListMock->expects($this->any())->method('asArray')->will($this->returnValue(array($path)));
        $mapperMock = $this->getMock('Magento_Acl_Loader_Resource_ConfigReader_Xml_ArrayMapper');
        $converterMock = $this->getMock('Magento_Config_Dom_Converter_ArrayConverter');
        $this->_configMock = $this->getMock('Mage_Core_Model_Config', array(), array(), '', false);
        $this->_configMock->expects($this->any())
            ->method('getModuleDir')
            ->with('etc', 'Mage_Webapi')
            ->will($this->returnValue($dirPath));

        $this->_reader = new Mage_Webapi_Model_Acl_Loader_Resource_ConfigReader(
            $fileListMock,
            $mapperMock,
            $converterMock,
            $this->_configMock
        );
    }

    public function testGetSchemaFile()
    {
        $actualXsdPath = $this->_reader->getSchemaFile();
        $this->assertInternalType('string', $actualXsdPath);
        $this->assertFileExists($actualXsdPath);
    }

    public function testGetVirtualResources()
    {
        $resources = $this->_reader->getAclVirtualResources();
        $this->assertEquals(1, $resources->length, 'More than one virtual resource.');
        $this->assertEquals('customer/list', $resources->item(0)->getAttribute('id'), 'Wrong id of virtual resource');
        $this->assertEquals('customer/get', $resources->item(0)->getAttribute('parent'),
            'Wrong parent id of virtual resource');
    }
}
