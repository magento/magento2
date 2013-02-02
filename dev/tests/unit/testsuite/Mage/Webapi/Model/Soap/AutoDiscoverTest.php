<?php
use Zend\Soap\Wsdl;

/**
 * SOAP AutoDiscover tests.
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
class Mage_Webapi_Model_Soap_AutoDiscoverTest extends PHPUnit_Framework_TestCase
{
    /** @var Mage_Webapi_Model_Soap_Wsdl */
    protected $_wsdlMock;

    /** @var Mage_Webapi_Model_Soap_AutoDiscover */
    protected $_autoDiscover;

    /** @var Mage_Core_Model_Cache */
    protected $_cacheMock;

    /** @var Mage_Webapi_Model_Config_Soap */
    protected $_resourceConfigMock;

    protected function setUp()
    {
        /** Prepare arguments for SUT constructor. */
        $this->_resourceConfigMock = $this->getMockBuilder('Mage_Webapi_Model_Config_Soap')
            ->disableOriginalConstructor()->getMock();

        $this->_wsdlMock = $this->getMockBuilder('Mage_Webapi_Model_Soap_Wsdl')
            ->disableOriginalConstructor()
            ->setMethods(
            array(
                'addSchemaTypeSection',
                'addService',
                'addPortType',
                'addBinding',
                'addSoapBinding',
                'addElement',
                'addComplexType',
                'addMessage',
                'addPortOperation',
                'addBindingOperation',
                'addSoapOperation',
                'toXML'
            )
        )
            ->getMock();
        $wsdlFactory = $this->getMock(
            'Mage_Webapi_Model_Soap_Wsdl_Factory',
            array('create'),
            array(new Magento_ObjectManager_Zend())
        );
        $wsdlFactory->expects($this->any())->method('create')->will($this->returnValue($this->_wsdlMock));
        $helper = $this->getMock('Mage_Webapi_Helper_Config', array('__'));
        $helper->expects($this->any())->method('__')->will($this->returnArgument(0));
        $this->_cacheMock = $this->getMockBuilder('Mage_Core_Model_Cache')->disableOriginalConstructor()->getMock();
        /** Initialize SUT. */
        $this->_autoDiscover = new Mage_Webapi_Model_Soap_AutoDiscover(
            $this->_resourceConfigMock,
            $wsdlFactory,
            $helper,
            $this->_cacheMock
        );
        parent::setUp();
    }

    protected function tearDown()
    {
        unset($this->_wsdlMock);
        unset($this->_autoDiscover);
        unset($this->_cacheMock);
        unset($this->_resourceConfigMock);
        parent::tearDown();
    }

    /**
     * Positive test case. Generate simple resource WSDL.
     *
     * @dataProvider generateDataProvider()
     */
    public function testGenerate($resourceName, $methodName, $interface)
    {
        $serviceDomMock = $this->_getDomElementMock();
        $this->_wsdlMock->expects($this->once())->method('addService')->will($this->returnValue($serviceDomMock));
        $portTypeDomMock = $this->_getDomElementMock();
        $portTypeName = $this->_autoDiscover->getPortTypeName($resourceName);
        $this->_wsdlMock->expects($this->once())
            ->method('addPortType')
            ->with($portTypeName)
            ->will($this->returnValue($portTypeDomMock));
        $bindingDomMock = $this->_getDomElementMock();
        $bindingName = $this->_autoDiscover->getBindingName($resourceName);
        $this->_wsdlMock->expects($this->once())
            ->method('addBinding')
            ->with($bindingName, Wsdl::TYPES_NS . ':' . $portTypeName)
            ->will($this->returnValue($bindingDomMock));
        $this->_wsdlMock->expects($this->once())
            ->method('addSoapBinding')
            ->with($bindingDomMock);
        $operationName = $this->_autoDiscover->getOperationName($resourceName, $methodName);
        $inputMessageName = $this->_autoDiscover->getInputMessageName($operationName);
        $outputMessageName = $this->_autoDiscover->getOutputMessageName($operationName);
        $this->_wsdlMock->expects($this->once())
            ->method('addPortOperation')
            ->with(
            $portTypeDomMock,
            $operationName,
            Wsdl::TYPES_NS . ':' . $inputMessageName,
            Wsdl::TYPES_NS . ':' . $outputMessageName
        );
        $operationDomMock = $this->_getDomElementMock();
        $this->_wsdlMock->expects($this->once())
            ->method('addBindingOperation')
            ->with(
            $bindingDomMock,
            $operationName,
            array('use' => 'literal'),
            array('use' => 'literal'),
            false,
            SOAP_1_2
        )
            ->will($this->returnValue($operationDomMock));
        $this->_wsdlMock->expects($this->once())
            ->method('addSoapOperation')
            ->with($operationDomMock, $operationName, SOAP_1_2);
        $this->_wsdlMock->expects($this->once())
            ->method('toXML');

        $requestedResources = array(
            $resourceName => array(
                'methods' => array(
                    $methodName => array(
                        'interface' => $interface,
                        'documentation' => 'test method A',
                    ),
                ),
            ),
        );
        $endpointUrl = 'http://magento.host/api/soap/';
        $this->_autoDiscover->generate($requestedResources, $endpointUrl);
    }

    /**
     * Test handle method with loading WSDL from cache.
     */
    public function testHandleLoadWsdlFromCache()
    {
        /** Mock cache canUse method to return true. */
        $this->_cacheMock->expects($this->once())->method('canUse')->will($this->returnValue(true));
        /** Mock cache load method to return cache ID. */
        $this->_cacheMock->expects($this->once())->method('load')->will($this->returnArgument(0));
        $requestedResources = array(
            'res1' => 'v1',
            'res2' => 'v2'
        );
        $result = $this->_autoDiscover->handle($requestedResources, 'http://magento.host');
        /** Assert that handle method will return string that starts with WSDL. */
        $this->assertStringStartsWith(
            Mage_Webapi_Model_Soap_AutoDiscover::WSDL_CACHE_ID,
            $result,
            'Wsdl is not loaded from cache.'
        );
    }

    /**
     * Test handle method with exception.
     */
    public function testHandleWithException()
    {
        /** Mock cache canUse method to return false. */
        $this->_cacheMock->expects($this->once())->method('canUse')->will($this->returnValue(false));
        $requestedResources = array('res1' => 'v1');
        $exception = new LogicException('getResourceDataMerged Exception');
        $this->_resourceConfigMock->expects($this->once())->method('getResourceDataMerged')->will(
            $this->throwException($exception)
        );
        $this->setExpectedException(
            'Mage_Webapi_Exception',
            'getResourceDataMerged Exception',
            Mage_Webapi_Exception::HTTP_BAD_REQUEST
        );
        $this->_autoDiscover->handle($requestedResources, 'http://magento.host');
    }

    /**
     * Data provider for generate() test.
     *
     * @return array
     */
    public static function generateDataProvider()
    {
        $simpleInterface = array(
            'in' => array(
                'parameters' => array(
                    'resource_id' => array(
                        'type' => 'int',
                        'required' => true,
                        'documentation' => 'Resource ID.{annotation:value}'
                    ),
                    'optional' => array(
                        'type' => 'boolean',
                        'required' => false,
                        'default' => true,
                        'documentation' => 'Optional parameter.'
                    ),
                ),
            ),
            'out' => array(
                'parameters' => array(
                    'result' => array(
                        'type' => 'string',
                        'required' => true,
                        'documentation' => 'Operation result.'
                    )
                ),
            ),
        );
        $oneWayInterface = array(
            'out' => array(
                'parameters' => array(
                    'result' => array(
                        'type' => 'string',
                        'required' => true,
                        'documentation' => 'Operation result.'
                    )
                ),
            ),
        );
        $complexTypeInterface = array(
            'in' => array(
                'parameters' => array(
                    'complex_param' => array(
                        'type' => 'ComplexTypeA',
                        'required' => true,
                        'documentation' => 'Optional complex type param.'
                    ),
                ),
            ),
            'out' => array(
                'parameters' => array(
                    'result' => array(
                        'type' => 'ComplexTypeB',
                        'required' => false,
                        'documentation' => 'Operation result.'
                    )
                ),
            ),
        );

        return array(
            'Method with simple parameters' => array('resource_a', 'methodB', $simpleInterface),
            'One-way method' => array('resource_a', 'methodC', $oneWayInterface),
            'Method with complex type in parameters' => array('resource_a', 'methodE', $complexTypeInterface),
        );
    }

    /**
     * Create mock for DOMElement.
     *
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    protected function _getDomElementMock()
    {
        return $this->getMockBuilder('DOMElement')
            ->disableOriginalConstructor()
            ->getMock();
    }
}
