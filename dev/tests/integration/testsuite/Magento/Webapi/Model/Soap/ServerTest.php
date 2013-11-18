<?php
/**
 * Test SOAP server model.
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
namespace Magento\Webapi\Model\Soap;

class ServerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_configScopeMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_areaListMock;

    /** @var \Magento\Webapi\Controller\Soap\Request */
    protected $_requestMock;

    /** @var \Magento\DomDocument\Factory */
    protected $_domDocumentFactory;

    /** @var \Magento\Core\Model\Store */
    protected $_storeMock;

    /** @var \Magento\Core\Model\StoreManagerInterface */
    protected $_storeManagerMock;

    /** @var \Magento\Webapi\Model\Soap\Server\Factory */
    protected $_soapServerFactory;

    protected function setUp()
    {
        $this->_storeManagerMock = $this->getMockBuilder('Magento\Core\Model\StoreManager')
            ->disableOriginalConstructor()->getMock();
        $this->_storeMock = $this->getMockBuilder('Magento\Core\Model\Store')
            ->disableOriginalConstructor()->getMock();

        $this->_areaListMock = $this->getMock('Magento\App\AreaList', array(), array(), '', false);
        $this->_configScopeMock = $this->getMock('Magento\Config\ScopeInterface');
        $this->_storeManagerMock->expects($this->any())
            ->method('getStore')->will($this->returnValue($this->_storeMock));
        $this->_requestMock = $this->getMockBuilder('Magento\Webapi\Controller\Soap\Request')
            ->disableOriginalConstructor()->getMock();
        $this->_domDocumentFactory = $this->getMockBuilder('Magento\DomDocument\Factory')
            ->disableOriginalConstructor()->getMock();
        $this->_soapServerFactory = $this->getMockBuilder('Magento\Webapi\Model\Soap\Server\Factory')
            ->disableOriginalConstructor()->getMock();

        parent::setUp();
    }

    /**
     * Test SOAP server construction with WSDL cache enabling.
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function testConstructEnableWsdlCache()
    {
        /** Mock getConfig method to return true. */
        $this->_storeMock->expects($this->once())->method('getConfig')->will($this->returnValue(true));
        /** Create SOAP server object. */
        $server = new \Magento\Webapi\Model\Soap\Server(
            $this->_areaListMock,
            $this->_configScopeMock,
            $this->_requestMock,
            $this->_domDocumentFactory,
            $this->_storeManagerMock,
            $this->_soapServerFactory
        );
        /** Assert that SOAP WSDL caching option was enabled after SOAP server initialization. */
        $this->assertTrue((bool)ini_get('soap.wsdl_cache_enabled'), 'WSDL caching was not enabled.');
    }

    /**
     * Test SOAP server construction with WSDL cache disabling.
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function testConstructDisableWsdlCache()
    {
        /** Mock getConfig method to return false. */
        $this->_storeMock->expects($this->once())->method('getConfig')->will($this->returnValue(false));
        /** Create SOAP server object. */
        $server = new \Magento\Webapi\Model\Soap\Server(
            $this->_areaListMock,
            $this->_configScopeMock,
            $this->_requestMock,
            $this->_domDocumentFactory,
            $this->_storeManagerMock,
            $this->_soapServerFactory
        );
        /** Assert that SOAP WSDL caching option was disabled after SOAP server initialization. */
        $this->assertFalse((bool)ini_get('soap.wsdl_cache_enabled'), 'WSDL caching was not disabled.');
    }
}
