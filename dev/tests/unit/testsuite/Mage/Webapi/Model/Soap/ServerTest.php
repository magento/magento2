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
class Mage_Webapi_Model_Soap_ServerTest extends PHPUnit_Framework_TestCase
{
    /** @var Mage_Webapi_Model_Soap_Server */
    protected $_soapServer;

    /** @var Mage_Core_Model_App */
    protected $_applicationMock;

    /** @var Mage_Core_Model_Store */
    protected $_storeMock;

    /** @var Mage_Webapi_Controller_Request_Soap */
    protected $_requestMock;

    /** @var Magento_DomDocument_Factory */
    protected $_domDocumentFactory;

    protected function setUp()
    {
        /** Init all dependencies for SUT. */
        $this->_storeMock = $this->getMockBuilder('Mage_Core_Model_Store')->disableOriginalConstructor()->getMock();
        $this->_applicationMock = $this->getMockBuilder('Mage_Core_Model_App')->disableOriginalConstructor()->getMock();
        $this->_applicationMock->expects($this->any())->method('getStore')->will($this->returnValue($this->_storeMock));
        $this->_requestMock = $this->getMockBuilder('Mage_Webapi_Controller_Request_Soap')->disableOriginalConstructor()
            ->getMock();
        $this->_domDocumentFactory = $this->getMockBuilder('Magento_DomDocument_Factory')
            ->disableOriginalConstructor()->getMock();

        /** Init SUT. */
        $this->_soapServer = new Mage_Webapi_Model_Soap_Server(
            $this->_applicationMock,
            $this->_requestMock,
            $this->_domDocumentFactory
        );

        parent::setUp();
    }

    protected function tearDown()
    {
        unset($this->_soapServer);
        unset($this->_applicationMock);
        unset($this->_requestMock);
        unset($this->_storeMock);
        parent::tearDown();
    }

    /**
     * Test getApiCharset method.
     */
    public function testGetApiCharset()
    {
        $this->_storeMock->expects($this->once())->method('getConfig')->will($this->returnValue('Windows-1251'));
        $this->assertEquals('Windows-1251', $this->_soapServer->getApiCharset(), 'Wrong API charset encoding getting.');
    }

    /**
     * Test getApiCharset method with default encoding.
     */
    public function testGetApiCharsetDefaultEncoding()
    {
        $this->_storeMock->expects($this->once())->method('getConfig')->will($this->returnValue(null));
        $this->assertEquals(
            Mage_Webapi_Model_Soap_Server::SOAP_DEFAULT_ENCODING,
            $this->_soapServer->getApiCharset(),
            'Wrong default API charset encoding getting.'
        );
    }

    /**
     * Test generateUri method with default parameter.
     */
    public function testGenerateUriDefault()
    {
        $this->_storeMock->expects($this->once())->method('getBaseUrl')->will(
            $this->returnValue('http://magento.com/')
        );
        $this->_requestMock->expects($this->once())->method('getRequestedResources')->will(
            $this->returnValue(array('res' => 'v1'))
        );
        $actualResult = $this->_soapServer->generateUri();
        $expectedResult = 'http://magento.com/api/soap?resources%5Bres%5D=v1';
        $this->assertEquals($expectedResult, $actualResult, 'Wrong URI generation with default parameter.');
    }

    /**
     * Test generateUri method.
     *
     * @dataProvider providerForGenerateUriTest
     */
    public function testGenerateUri($isWsdl, $resources, $expectedUri, $assertMessage)
    {
        $this->_storeMock->expects($this->once())->method('getBaseUrl')->will(
            $this->returnValue('http://magento.com/')
        );
        $this->_requestMock->expects($this->once())->method('getRequestedResources')->will(
            $this->returnValue($resources)
        );
        $actualUri = $this->_soapServer->generateUri($isWsdl);
        $this->assertEquals($expectedUri, $actualUri, $assertMessage);
    }

    /**
     * Test getEndpointUri method.
     */
    public function testGetEndpointUri()
    {
        $this->_storeMock->expects($this->once())->method('getBaseUrl')->will(
            $this->returnValue('http://magento.com/')
        );
        $expectedResult = 'http://magento.com/' . Mage_Webapi_Controller_Router_Route_Webapi::API_AREA_NAME . '/'
            . Mage_Webapi_Controller_Front::API_TYPE_SOAP;
        $actualResult = $this->_soapServer->getEndpointUri();
        $this->assertEquals($expectedResult, $actualResult, 'Wrong endpoint URI building.');
    }

    /**
     * Test fault method with Exception.
     */
    public function testExceptionFault()
    {
        /** Init Exception. */
        $exception = new Exception();
        $faultResult = $this->_soapServer->fault($exception);
        /** Assert returned object is instance of SoapFault class. */
        $this->assertInstanceOf('SoapFault', $faultResult, 'SoapFault was not returned.');
    }

    /**
     * Test fault method with Mage_Webapi_Model_Soap_Fault.
     */
    public function testWebapiSoapFault()
    {
        /** Mock Webapi Soap fault. */
        $apiFault = $this->getMockBuilder('Mage_Webapi_Model_Soap_Fault')->disableOriginalConstructor()->getMock();
        /** Assert mocked fault toXml method will be executed once. */
        $apiFault->expects($this->once())->method('toXml');
        $this->_soapServer->fault($apiFault);
    }

    /**
     * Data provider for generateUri test.
     */
    public function providerForGenerateUriTest()
    {
        return array(
            //Each array contains isWsdl flag, resources, expected URI and assert message.
            'Several resources' => array(
                false,
                array('customer' => 'v1', 'product' => 'v2'),
                'http://magento.com/api/soap?resources%5Bcustomer%5D=v1&resources%5Bproduct%5D=v2',
                'Wrong URI generation with several resources.'
            ),
            'Several resources with WSDL' => array(
                true,
                array('customer' => 'v1', 'product' => 'v2'),
                'http://magento.com/api/soap?resources%5Bcustomer%5D=v1&resources%5Bproduct%5D=v2&wsdl=1',
                'Wrong URI generation with several resources and WSDL.'
            ),
            'Empty resources list' => array(
                true,
                array(),
                'http://magento.com/api/soap?wsdl=1',
                'Wrong URI generation without resources.'
            ),
        );
    }
}
