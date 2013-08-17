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
class Magento_Acl_Loader_Resource_ConfigReader_XmlTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Magento_Acl_Loader_Resource_ConfigReader_Xml
     */
    protected $_model;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_mapperMock;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_converterMock;

    public function setUp()
    {
        $files = array(
            realpath(__DIR__) . '/../../../_files/acl_1.xml',
            realpath(__DIR__) . '/../../../_files/acl_2.xml'
        );
        $fileListMock = $this->getMock('Magento_Acl_Loader_Resource_ConfigReader_FileListInterface');
        $fileListMock->expects($this->any())->method('asArray')->will($this->returnValue($files));

        $this->_mapperMock = new Magento_Acl_Loader_Resource_ConfigReader_Xml_ArrayMapper();
        $this->_converterMock = new Magento_Config_Dom_Converter_ArrayConverter();
        $this->_model = new Magento_Acl_Loader_Resource_ConfigReader_Xml(
            $fileListMock,
            $this->_mapperMock,
            $this->_converterMock
        );
    }

    public function testGetAclResources()
    {
        $resources = $this->_model->getAclResources();
        $this->assertNotEmpty($resources);
    }

    public function testGetAclResourcesMergedCorrectly()
    {
        $expectedResources = include realpath(__DIR__) . '/../../../_files/acl_merged.php';

        $actualResources = $this->_model->getAclResources();
        $this->assertNotEmpty($actualResources);
        $this->assertEquals($expectedResources, $actualResources);
    }

    public function testGetSchemaFile()
    {
        $this->assertFileExists($this->_model->getSchemaFile());
    }
}
