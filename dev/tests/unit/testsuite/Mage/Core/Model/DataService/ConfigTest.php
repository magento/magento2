<?php
/**
 * Mage_Core_Model_DataService_Config
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
class Mage_Core_Model_DataService_ConfigTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Core_Model_DataService_Config
     */
    protected $_dataServiceConfig;

    /** @var Mage_Core_Model_DataService_Config_Reader_Factory */
    private $_readersFactoryMock;

    public function setUp()
    {
        $reader = $this->getMockBuilder('Mage_Core_Model_DataService_Config_Reader')
            ->disableOriginalConstructor()
            ->getMock();

        $configXml = file_get_contents(__DIR__ . '/_files/service_calls.xml');
        $config = new Magento_Config_Dom($configXml);
        $reader->expects($this->any())
            ->method('getServiceCallConfig')
            ->will($this->returnValue($config->getDom()));

        $this->_readersFactoryMock = $this->getMockBuilder('Mage_Core_Model_DataService_Config_Reader_Factory')
            ->disableOriginalConstructor()
            ->getMock();

        $this->_readersFactoryMock->expects($this->any())
            ->method('createReader')
            ->will($this->returnValue($reader));

        /** @var Mage_Core_Model_Config_Modules_Reader $modulesReaderMock */
        $modulesReaderMock = $this->getMockBuilder('Mage_Core_Model_Config_Modules_Reader')
            ->disableOriginalConstructor()
            ->getMock();
        $modulesReaderMock->expects($this->any())
            ->method('getModuleConfigurationFiles')
            ->will($this->returnValue(array()));

        $this->_dataServiceConfig = new Mage_Core_Model_DataService_Config(
            $this->_readersFactoryMock, $modulesReaderMock);
    }

    public function testGetClassByAlias()
    {
        // result should match the config.xml file
        $result = $this->_dataServiceConfig->getClassByAlias('alias');
        $this->assertNotNull($result);
        $this->assertEquals('some_class_name', $result['class']);
        $this->assertEquals('some_method_name', $result['retrieveMethod']);
        $this->assertEquals('foo', $result['methodArguments']['some_arg_name']);
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Service call with name
     */
    public function testGetClassByAliasNotFound()
    {
        $this->_dataServiceConfig->getClassByAlias('none');
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage
     */
    public function testGetClassByAliasInvalidCall()
    {
        $this->_dataServiceConfig->getClassByAlias('missing_service');
    }

    /**
     * @expectedException LogicException
     * @expectedExceptionMessage
     */
    public function testGetClassByAliasMethodNotFound()
    {
        $this->_dataServiceConfig->getClassByAlias('missing_retrieval_method');
    }

}
