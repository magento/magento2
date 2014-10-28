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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\AdminNotification\Model\System\Message;

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
        $helper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->_configMock = $this->getMock('Magento\Framework\App\Config', array(), array(), '', false);
        $this->_urlBuilderMock = $this->getMock('Magento\Framework\UrlInterface');

        $this->_storeManagerMock = $this->getMock('Magento\Framework\StoreManagerInterface');
        $configFactoryMock = $this->getMock(
            'Magento\Framework\App\Config\ValueFactory',
            array('create'),
            array(),
            '',
            false
        );
        $this->_configDataMock = $this->getMock(
            'Magento\Framework\App\Config\Value',
            array('getScope', 'getScopeId', 'getCollection', '__sleep', '__wakeup'),
            array(),
            '',
            false
        );
        $this->_dataCollectionMock = $this->getMock(
            'Magento\Core\Model\Resource\Config\Data\Collection',
            array(),
            array(),
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

        $arguments = array(
            'config' => $this->_configMock,
            'urlBuilder' => $this->_urlBuilderMock,
            'configValueFactory' => $configFactoryMock,
            'storeManager' => $this->_storeManagerMock
        );
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
        $map = array(
            array(
                \Magento\Store\Model\Store::XML_PATH_UNSECURE_BASE_URL,
                'default',
                null,
                \Magento\Store\Model\Store::BASE_URL_PLACEHOLDER
            ),
            array(
                \Magento\Store\Model\Store::XML_PATH_SECURE_BASE_URL,
                'default',
                null,
                \Magento\Store\Model\Store::BASE_URL_PLACEHOLDER
            )
        );
        $this->_configMock->expects($this->exactly(2))->method('getValue')->will($this->returnValueMap($map));
        $this->_urlBuilderMock->expects(
            $this->once()
        )->method(
            'getUrl'
        )->with(
            'adminhtml/system_config/edit',
            array('section' => 'web')
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

        $storeMock = $this->getMock('Magento\Store\Model\Store', array(), array(), '', false);
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
            array('section' => 'web', $urlParam => 'some_code')
        )->will(
            $this->returnValue('http://some_url')
        );

        $this->assertContains('http://some_url', (string)$this->_model->getText());
    }

    public function getValueUrlWithSavedDataForStoreScopeDataProvider()
    {
        return array(
            'storeScope' => array('stores', 'store', 'getStore'),
            'websiteScope' => array('websites', 'website', 'getWebsite')
        );
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
