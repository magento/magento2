<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AdminNotification\Test\Unit\Model\System\Message;

use Magento\Store\Model\Store;

class BaseurlTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\AdminNotification\Model\System\Message\Baseurl
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_configMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_urlBuilderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_dataCollectionMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_configDataMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_iteratorMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_storeManagerMock;

    protected function setUp()
    {
        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->_configMock = $this->getMock('Magento\Framework\App\Config', [], [], '', false);
        $this->_urlBuilderMock = $this->getMock('Magento\Framework\UrlInterface');

        $this->_storeManagerMock = $this->getMock('Magento\Store\Model\StoreManagerInterface');
        $configFactoryMock = $this->getMock(
            'Magento\Framework\App\Config\ValueFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->_configDataMock = $this->getMock(
            'Magento\Framework\App\Config\Value',
            ['getScope', 'getScopeId', 'getCollection', '__sleep', '__wakeup'],
            [],
            '',
            false
        );
        $this->_dataCollectionMock = $this->getMock(
            'Magento\Config\Model\ResourceModel\Config\Data\Collection',
            [],
            [],
            '',
            false
        );

        $this->_iteratorMock = $this->getMock('Iterator');
        $this->_dataCollectionMock->expects(
            $this->any()
        )->method(
            'getIterator'
        )->will(
            $this->returnValue($this->_iteratorMock)
        );

        $configFactoryMock->expects($this->any())->method('create')->will($this->returnValue($this->_configDataMock));
        $this->_configDataMock->expects(
            $this->any()
        )->method(
            'getCollection'
        )->will(
            $this->returnValue($this->_dataCollectionMock)
        );

        $arguments = [
            'config' => $this->_configMock,
            'urlBuilder' => $this->_urlBuilderMock,
            'configValueFactory' => $configFactoryMock,
            'storeManager' => $this->_storeManagerMock,
        ];
        $this->_model = $helper->getObject('Magento\AdminNotification\Model\System\Message\Baseurl', $arguments);
    }

    public function testGetSeverity()
    {
        $this->assertEquals(
            \Magento\Framework\Notification\MessageInterface::SEVERITY_CRITICAL,
            $this->_model->getSeverity(),
            'Invalid message severity type'
        );
    }

    public function testgetValueUrlWithDefaultUnsecureAndSecureBaseUrl()
    {
        $map = [
            [
                Store::XML_PATH_UNSECURE_BASE_URL,
                'default',
                null,
                \Magento\Store\Model\Store::BASE_URL_PLACEHOLDER,
            ],
            [
                Store::XML_PATH_SECURE_BASE_URL,
                'default',
                null,
                \Magento\Store\Model\Store::BASE_URL_PLACEHOLDER
            ],
        ];
        $this->_configMock->expects($this->exactly(2))->method('getValue')->will($this->returnValueMap($map));
        $this->_urlBuilderMock->expects(
            $this->once()
        )->method(
            'getUrl'
        )->with(
            'adminhtml/system_config/edit',
            ['section' => 'web']
        )->will(
            $this->returnValue('http://some_url')
        );

        $this->assertContains('http://some_url', (string)$this->_model->getText());
    }

    public function testgetValueUrlWithoutSavedData()
    {
        $this->_configMock->expects($this->any())->method('getNode')->will($this->returnValue(null));
        $this->_urlBuilderMock->expects($this->never())->method('getUrl');
    }

    /**
     * @dataProvider getValueUrlWithSavedDataForStoreScopeDataProvider
     */
    public function testgetValueUrlWithSavedDataForScopes($scope, $urlParam, $storeMethod)
    {
        $this->_configMock->expects($this->any())->method('getNode')->will($this->returnValue(null));
        $this->_iteratorMock->expects($this->once())->method('valid')->will($this->returnValue(true));
        $this->_iteratorMock->expects(
            $this->once()
        )->method(
            'current'
        )->will(
            $this->returnValue($this->_configDataMock)
        );

        $this->_configDataMock->expects($this->once())->method('getScopeId')->will($this->returnValue(1));

        $storeMock = $this->getMock('Magento\Store\Model\Store', [], [], '', false);
        $this->_storeManagerMock->expects(
            $this->once()
        )->method(
            $storeMethod
        )->with(
            1
        )->will(
            $this->returnValue($storeMock)
        );
        $storeMock->expects($this->once())->method('getCode')->will($this->returnValue('some_code'));

        $this->_configDataMock->expects($this->any())->method('getScope')->will($this->returnValue($scope));
        $this->_urlBuilderMock->expects(
            $this->once()
        )->method(
            'getUrl'
        )->with(
            'adminhtml/system_config/edit',
            ['section' => 'web', $urlParam => 'some_code']
        )->will(
            $this->returnValue('http://some_url')
        );

        $this->assertContains('http://some_url', (string)$this->_model->getText());
    }

    public function getValueUrlWithSavedDataForStoreScopeDataProvider()
    {
        return [
            'storeScope' => ['stores', 'store', 'getStore'],
            'websiteScope' => ['websites', 'website', 'getWebsite']
        ];
    }

    public function testIsDisplayedWithEmptyConfigUrl()
    {
        $this->_configMock->expects(
            $this->any()
        )->method(
            'getValue'
        )->will(
            $this->returnValue(\Magento\Store\Model\Store::BASE_URL_PLACEHOLDER)
        );
        $this->_urlBuilderMock->expects($this->once())->method('getUrl')->will($this->returnValue(''));
        $this->assertFalse($this->_model->isDisplayed());
    }

    public function testIsDisplayedWithNotEmptyConfigUrl()
    {
        $this->_configMock->expects(
            $this->any()
        )->method(
            'getValue'
        )->will(
            $this->returnValue(\Magento\Store\Model\Store::BASE_URL_PLACEHOLDER)
        );
        $this->_urlBuilderMock->expects($this->once())->method('getUrl')->will($this->returnValue('http://some_url'));
        $this->assertTrue($this->_model->isDisplayed());
    }

    public function testGetIdentity()
    {
        $this->_configMock->expects(
            $this->any()
        )->method(
            'getValue'
        )->will(
            $this->returnValue(\Magento\Store\Model\Store::BASE_URL_PLACEHOLDER)
        );
        $this->_urlBuilderMock->expects($this->once())->method('getUrl')->will($this->returnValue('some_url'));
        $this->assertEquals(md5('BASE_URLsome_url'), $this->_model->getIdentity());
    }

    public function testGetText()
    {
        $expected = '{{base_url}} is not recommended to use in a production environment';
        $this->assertContains($expected, (string)$this->_model->getText());
    }
}
