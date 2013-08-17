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
class Mage_AdminNotification_Model_System_Message_BaseurlTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_AdminNotification_Model_System_Message_Baseurl
     */
    protected $_model;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_configMock;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_urlBuilderMock;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_helperFactoryMock;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_helperMock;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_dataCollectionMock;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_configDataMock;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_iteratorMock;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_storeManagerMock;

    protected function setUp()
    {
        $helper = new Magento_Test_Helper_ObjectManager($this);
        $this->_configMock = $this->getMock('Mage_Core_Model_Config', array(), array(), '', false);
        $this->_urlBuilderMock = $this->getMock('Mage_Core_Model_UrlInterface');
        $this->_helperFactoryMock = $this->getMock('Mage_Core_Model_Factory_Helper', array(), array(), '', false);
        $this->_helperMock = $this->getMock('Mage_AdminNotification_Helper_Data', array(), array(), '', false);
        $this->_helperFactoryMock->expects($this->any())->method('get')
            ->with('Mage_AdminNotification_Helper_Data')->will($this->returnValue($this->_helperMock));

        $this->_storeManagerMock = $this->getMock('Mage_Core_Model_StoreManagerInterface');
        $configFactoryMock = $this->getMock('Mage_Core_Model_Config_DataFactory', array('create'),
            array(), '', false
        );
        $this->_configDataMock = $this->getMock('Mage_Core_Model_Config_Data',
            array('getScope', 'getScopeId', 'getCollection'),
            array(), '', false
        );
        $this->_dataCollectionMock = $this->getMock('Mage_Core_Model_Resource_Config_Data_Collection',
            array(), array(), '', false
        );

        $this->_iteratorMock = $this->getMock('Iterator');
        $this->_dataCollectionMock->expects($this->any())
            ->method('getIterator')->will($this->returnValue($this->_iteratorMock));

        $configFactoryMock->expects($this->any())
            ->method('create')->will($this->returnValue($this->_configDataMock));
        $this->_configDataMock->expects($this->any())
            ->method('getCollection')->will($this->returnValue($this->_dataCollectionMock));

        $arguments = array(
            'config' => $this->_configMock,
            'urlBuilder' => $this->_urlBuilderMock,
            'helperFactory' => $this->_helperFactoryMock,
            'configDataFactory' => $configFactoryMock,
            'storeManager' => $this->_storeManagerMock,
        );
        $this->_model = $helper->getObject('Mage_AdminNotification_Model_System_Message_Baseurl', $arguments);
    }

    public function testGetSeverity()
    {
        $this->assertEquals(
            Mage_AdminNotification_Model_System_MessageInterface::SEVERITY_CRITICAL,
            $this->_model->getSeverity(),
            'Invalid message severity type'
        );
    }

    public function testGetConfigUrlWithDefaultUnsecureAndSecureBaseUrl()
    {
        $map = array(
            array('default/' . Mage_Core_Model_Store::XML_PATH_UNSECURE_BASE_URL, '', null,
                Mage_Core_Model_Store::BASE_URL_PLACEHOLDER
            ),
            array('default/' . Mage_Core_Model_Store::XML_PATH_SECURE_BASE_URL, '', null,
                Mage_Core_Model_Store::BASE_URL_PLACEHOLDER
            ),
        );
        $this->_configMock->expects($this->exactly(2))->method('getNode')->will($this->returnValueMap($map));
        $this->_urlBuilderMock->expects($this->once())
            ->method('getUrl')
            ->with('adminhtml/system_config/edit', array('section' => 'web'))
            ->will($this->returnValue('http://some_url'));
        $this->_helperMock->expects($this->any())->method('__')->will($this->returnArgument(1));

        $this->assertStringEndsWith('http://some_url', $this->_model->getText());
    }

    public function testGetConfigUrlWithoutSavedData()
    {
        $this->_configMock->expects($this->any())->method('getNode')->will($this->returnValue(null));
        $this->_urlBuilderMock->expects($this->never())->method('getUrl');
        $this->_helperMock->expects($this->any())->method('__')->will($this->returnArgument(1));
        $this->assertEquals('', $this->_model->getText());
    }

    /**
     * @dataProvider getConfigUrlWithSavedDataForStoreScopeDataProvider
     */
    public function testGetConfigUrlWithSavedDataForScopes($scope, $urlParam, $storeMethod)
    {
        $this->_configMock->expects($this->any())->method('getNode')->will($this->returnValue(null));
        $this->_iteratorMock->expects($this->once())->method('valid')->will($this->returnValue(true));
        $this->_iteratorMock->expects($this->once())->method('current')
            ->will($this->returnValue($this->_configDataMock));

        $this->_configDataMock->expects($this->once())->method('getScopeId')->will($this->returnValue(1));

        $storeMock = $this->getMock('Mage_Core_Model_Store', array(), array(), '', false);
        $this->_storeManagerMock->expects($this->once())->method($storeMethod)
            ->with(1)->will($this->returnValue($storeMock));
        $storeMock->expects($this->once())->method('getCode')->will($this->returnValue('some_code'));

        $this->_configDataMock->expects($this->any())->method('getScope')->will($this->returnValue($scope));
        $this->_urlBuilderMock->expects($this->once())
            ->method('getUrl')
            ->with('adminhtml/system_config/edit', array('section' => 'web', $urlParam => 'some_code'))
            ->will($this->returnValue('http://some_url'));
        $this->_helperMock->expects($this->any())->method('__')->will($this->returnArgument(1));

        $this->assertEquals('http://some_url', $this->_model->getText());
    }

    public function getConfigUrlWithSavedDataForStoreScopeDataProvider()
    {
        return array(
            'storeScope' => array('stores', 'store', 'getStore'),
            'websiteScope' => array('websites', 'website', 'getWebsite'),
        );
    }

    public function testIsDisplayedWithEmptyConfigUrl()
    {
        $this->_configMock->expects($this->any())->method('getNode')
            ->will($this->returnValue(Mage_Core_Model_Store::BASE_URL_PLACEHOLDER));
        $this->_urlBuilderMock->expects($this->once())->method('getUrl')->will($this->returnValue(''));
        $this->assertFalse($this->_model->isDisplayed());
    }

    public function testIsDisplayedWithNotEmptyConfigUrl()
    {
        $this->_configMock->expects($this->any())->method('getNode')
            ->will($this->returnValue(Mage_Core_Model_Store::BASE_URL_PLACEHOLDER));
        $this->_urlBuilderMock->expects($this->once())->method('getUrl')->will($this->returnValue('http://some_url'));
        $this->assertTrue($this->_model->isDisplayed());
    }

    public function testGetIdentity()
    {
        $this->_configMock->expects($this->any())->method('getNode')
            ->will($this->returnValue(Mage_Core_Model_Store::BASE_URL_PLACEHOLDER));
        $this->_urlBuilderMock->expects($this->once())->method('getUrl')->will($this->returnValue('some_url'));
        $this->assertEquals(md5('BASE_URLsome_url'), $this->_model->getIdentity());
    }

    public function testGetText()
    {

        $expected = 'some text';
        $this->_helperMock->expects($this->once())
            ->method('__')
            ->with($this->stringStartsWith('{{base_url}} is not recommended'))
            ->will($this->returnValue('some text'));
        $this->assertEquals($expected, $this->_model->getText());
    }
}
