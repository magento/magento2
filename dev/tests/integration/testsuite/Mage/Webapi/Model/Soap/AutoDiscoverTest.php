<?php
use Zend\Soap\Wsdl;

/**
 * SOAP AutoDiscover integration tests.
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
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**#@+
 * Data structures should be available without auto loader as the file name cannot be calculated from class name.
 */
include __DIR__ . '/../../_files/Model/Webapi/ModuleB/Subresource/SubresourceData.php';
include __DIR__ . '/../../_files/Model/Webapi/ModuleB/ModuleBData.php';
include __DIR__ . '/../../_files/Controller/AutoDiscover/ModuleB.php';
/**#@-*/

/**
 * Class for {@see Mage_Webapi_Model_Soap_AutoDiscover} model testing.
 */
class Mage_Webapi_Model_Soap_AutoDiscoverTest extends PHPUnit_Framework_TestCase
{
    /** @var Mage_Webapi_Model_Config_Soap */
    protected $_config;

    /** @var Mage_Webapi_Model_Soap_AutoDiscover */
    protected $_autoDiscover;

    /** @var Mage_Webapi_Helper_Config */
    protected $_helper;

    /**
     * Name of the resource under the test.
     *
     * @var string
     */
    protected $_resourceName;

    /**
     * Resource under the test data from config.
     *
     * @var array
     */
    protected $_resourceData;

    /**
     * DOMDocument containing generated WSDL.
     *
     * @var DOMDocument
     */
    protected $_dom;

    /**
     * DOMXpath containing generated WSDL DOM.
     *
     * @var DOMXPath
     */
    protected $_xpath;

    /**
     * Set up config with fixture controllers directory scanner
     */
    protected function setUp()
    {
        $fixtureDir = __DIR__ . '/../../_files/Controller/AutoDiscover/';
        $directoryScanner = new \Zend\Code\Scanner\DirectoryScanner($fixtureDir);
        /** @var Mage_Core_Model_App $app */
        $app = $this->getMockBuilder('Mage_Core_Model_App')->disableOriginalConstructor()->getMock();
        $objectManager = new Magento_Test_ObjectManager();
        $this->_helper = $objectManager->get('Mage_Webapi_Helper_Config');
        $reader = $objectManager->get('Mage_Webapi_Model_Config_Reader_Soap');
        $reader->setDirectoryScanner($directoryScanner);
        $this->_config = new Mage_Webapi_Model_Config_Soap($reader, $this->_helper, $app);
        $objectManager->addSharedInstance($this->_config, 'Mage_Webapi_Model_Config_Soap');
        $wsdlFactory = new Mage_Webapi_Model_Soap_Wsdl_Factory($objectManager);
        $cache = $this->getMockBuilder('Mage_Core_Model_Cache')->disableOriginalConstructor()->getMock();
        $this->_autoDiscover = new Mage_Webapi_Model_Soap_AutoDiscover(
            $this->_config,
            $wsdlFactory,
            $this->_helper,
            $cache
        );

        $this->_resourceName = 'vendorModuleB';
        $this->_resourceData = $this->_config->getResourceDataMerged($this->_resourceName, 'v1');
        $xml = $this->_autoDiscover->generate(array($this->_resourceName => $this->_resourceData),
            'http://magento.host/api/soap');
        $this->_dom = new DOMDocument('1.0', 'utf-8');
        $this->_dom->loadXML($xml);
        $this->_xpath = new DOMXPath($this->_dom);
        $this->_xpath->registerNamespace(Wsdl::WSDL_NS, Wsdl::WSDL_NS_URI);

        parent::setUp();
    }

    /**
     * Test WSDL operations Generation.
     * Generate WSDL XML using AutoDiscover and prepared config.
     * Walk through all methods from "vendorModuleB resource" (_files/controllers/AutoDiscover/ModuleBController.php)
     * Assert that service, portType and binding has been generated correctly for resource.
     * Assert that each method from controller has generated operations in portType and binding nodes.
     * Assert that each method has input and output messages and complexTypes generated correctly.
     */
    public function testGenerateOperations()
    {
        $this->_assertServiceNode();
        $binding = $this->_assertBinding();
        $portType = $this->_assertPortType();

        foreach ($this->_resourceData['methods'] as $methodName => $methodData) {
            $operationName = $this->_autoDiscover->getOperationName($this->_resourceName, $methodName);
            $this->_assertBindingOperation($operationName, $binding);
            $portOperation = $this->_assertPortTypeOperation($operationName, $portType);
            // Assert portType operation input
            /** @var DOMElement $operationInput */
            $operationInput = $portOperation->getElementsByTagName('input')->item(0);
            $this->assertNotNull($operationInput, sprintf('"input" node was not found in "%s" port operation.',
                $operationName));
            $messageName = $this->_autoDiscover->getInputMessageName($operationName);
            $operationRequest = $this->_assertMessage($operationInput, $messageName, $methodData['documentation']);
            if (isset($methodData['interface']['in'])) {
                foreach ($methodData['interface']['in']['parameters'] as $parameterName => $parameterData) {
                    $expectedAppinfo = array(
                        'callInfo' => array(
                            $operationName => array(
                                'requiredInput' => $parameterData['required'] ? 'Yes' : 'No'
                            )
                        ),
                    );
                    $this->_assertParameter($parameterName, $parameterData['type'], $parameterData['required'],
                        $parameterData['documentation'], $expectedAppinfo, $operationRequest);
                }
            }
            // Assert portType operation output
            if (isset($methodData['interface']['out'])) {
                /** @var DOMElement $operationOutput */
                $operationOutput = $portOperation->getElementsByTagName('output')->item(0);
                $this->assertNotNull($operationOutput, sprintf('"output" node was not found in "%s" port operation.',
                    $operationName));
                $messageName = $this->_autoDiscover->getInputMessageName($operationName);
                $expectedDoc = sprintf('Response container for the %s call.', $operationName);
                $operationResponse = $this->_assertMessage($operationOutput, $messageName, $expectedDoc);
                foreach ($methodData['interface']['out']['parameters'] as $parameterName => $parameterData) {
                    $expectedAppinfo = array(
                        'callInfo' => array(
                            $operationName => array(
                                'returned' => $parameterData['required'] ? 'Always' : 'Conditionally'
                            ),
                        ),
                    );
                    $this->_assertParameter($parameterName, $parameterData['type'], $parameterData['required'],
                        $parameterData['documentation'], $expectedAppinfo, $operationResponse);
                }
            }
        }
    }

    /**
     * Test that complexType for Data structures has been generated correctly in WSDL.
     * See /_files/controllers/AutoDiscover/ModuleB/SubresourceData.php
     */
    public function testGenerateDataStructureComplexTypes()
    {
        $wsdlNs = Wsdl::WSDL_NS;
        $xsdNs = Wsdl::XSD_NS;

        // Generated from Vendor_ModuleB_Model_Webapi_ModuleBData class.
        $dataStructureName = 'VendorModuleBData';
        $typeData = $this->_config->getTypeData($dataStructureName);
        $complexTypeXpath = "//{$wsdlNs}:types/{$xsdNs}:schema/{$xsdNs}:complexType[@name='%s']";
        /** @var DOMElement $dataStructure */
        $dataStructure = $this->_xpath->query(sprintf($complexTypeXpath, $dataStructureName))->item(0);
        $this->assertNotNull($dataStructure, sprintf('Complex type for data structure "%s" was not found in WSDL.',
            $dataStructureName));
        $this->_assertDocumentation($typeData['documentation'], $dataStructure);
        // Expected appinfo tags.
        $expectedAppinfo = include __DIR__ . '/../../_files/Controller/annotation_fixture.php';

        foreach ($typeData['parameters'] as $parameterName => $parameterData) {
            // remove all appinfo placeholders from expected doc.
            $expectedDoc = trim(preg_replace('/(\{.*\}|\\r)/U', '', $parameterData['documentation']));
            $this->_assertParameter($parameterName, $parameterData['type'], $parameterData['required'],
                $expectedDoc, $expectedAppinfo[$parameterName], $dataStructure);
        }
    }

    /**
     * Assert parameter data.
     *
     * @param string $expectedName
     * @param string $expectedType
     * @param string $expectedIsRequired
     * @param string $expectedDoc
     * @param array $expectedAppinfo
     * @param DOMElement $complexType with actual parameter element.
     */
    protected function _assertParameter($expectedName, $expectedType, $expectedIsRequired, $expectedDoc,
        $expectedAppinfo, DOMElement $complexType
    ) {
        $xsdNs = Wsdl::XSD_NS;
        $tns = Wsdl::TYPES_NS;
        /** @var DOMElement $parameterElement */
        $parameterElement = $this->_xpath->query("{$xsdNs}:sequence/{$xsdNs}:element[@name='{$expectedName}']",
            $complexType)->item(0);
        $this->assertNotNull($parameterElement, sprintf('"%s" element was not found in complex type "%s".',
            $expectedName, $complexType->getAttribute('name')));
        $isArray = $this->_helper->isArrayType($expectedType);
        if ($isArray) {
            $expectedType = $this->_helper->translateArrayTypeName($expectedType);
        } else {
            $this->assertEquals($expectedIsRequired ? 1 : 0, $parameterElement->getAttribute('minOccurs'));
            $this->assertEquals(1, $parameterElement->getAttribute('maxOccurs'));
        }
        $expectedNs = $this->_helper->isTypeSimple($expectedType) ? $xsdNs : $tns;
        $this->assertEquals("{$expectedNs}:{$expectedType}", $parameterElement->getAttribute('type'));
        $this->_assertDocumentation($expectedDoc, $parameterElement);
        $this->_assertAppinfo($expectedAppinfo, $parameterElement);
    }

    /**
     * Assert appinfo nodes in given element.
     *
     * @param array $expectedAppinfo
     * @param DOMElement $element with actual appinfo node
     */
    protected function _assertAppinfo($expectedAppinfo, DOMElement $element)
    {
        $xsdNs = Wsdl::XSD_NS;
        $infNs = Mage_Webapi_Model_Soap_Wsdl_ComplexTypeStrategy_ConfigBased::APP_INF_NS;
        /** @var DOMElement $appInfoNode */
        $appInfoNode = $this->_xpath->query("{$xsdNs}:annotation/{$xsdNs}:appinfo", $element)->item(0);
        $elementName = $element->getAttribute('name');
        $this->assertNotNull($appInfoNode, sprintf('"appinfo" node not found in "%s" element.', $elementName));

        foreach ($expectedAppinfo as $appInfoKey => $appInfoData) {
            switch ($appInfoKey) {
                case 'callInfo':
                    $this->_assertCallInfo($appInfoNode, $appInfoData);
                    break;
                case 'seeLink':
                    $this->_assertSeeLink($appInfoNode, $appInfoData);
                    break;
                case 'docInstructions':
                    $this->_assertDocInstructions($appInfoNode, $appInfoData);
                    break;
                default:
                    $tagNode = $this->_xpath->query($infNs . ':' . $appInfoKey, $appInfoNode)->item(0);
                    $this->assertNotNull($tagNode, sprintf('Appinfo node "%s" was not found in element "%s"',
                        $appInfoKey, $elementName));
                    $this->assertEquals($appInfoData, $tagNode->nodeValue, sprintf('Appinfo node "%s" is not correct.',
                        $appInfoKey));
                    break;
            }
        }
    }

    /**
     * Assert docInstructions appinfo node and it's subnodes.
     *
     * @param DOMElement $appInfoNode
     * @param array $appInfoData
     */
    protected function _assertDocInstructions(DOMElement $appInfoNode, array $appInfoData)
    {
        $infNs = Mage_Webapi_Model_Soap_Wsdl_ComplexTypeStrategy_ConfigBased::APP_INF_NS;
        $elementName = $appInfoNode->parentNode->parentNode->getAttribute('name');
        /** @var DOMElement $docInstructionsNode */
        $docInstructionsNode = $this->_xpath->query("{$infNs}:docInstructions", $appInfoNode)->item(0);
        $this->assertNotNull($docInstructionsNode,
            sprintf('"docInstructions" node was not found in "%s" element appinfo.', $elementName));
        foreach ($appInfoData as $direction => $value) {
            /** @var DOMElement $subNode */
            $subNode = $this->_xpath->query("{$infNs}:{$direction}/{$infNs}:{$value}", $docInstructionsNode)->item(0);
            $this->assertNotNull($subNode,
                sprintf('"%s/%s" node  was not found in "%s" element appinfo "docInstructions".',
                    $direction, $value, $elementName));
        }
    }

    /**
     * Assert 'seeLink' annotation node and it's subnodes.
     *
     * @param DOMElement $appInfoNode
     * @param array $appInfoData
     */
    protected function _assertSeeLink(DOMElement $appInfoNode, array $appInfoData)
    {
        $infNs = Mage_Webapi_Model_Soap_Wsdl_ComplexTypeStrategy_ConfigBased::APP_INF_NS;
        $elementName = $appInfoNode->parentNode->parentNode->getAttribute('name');
        /** @var DOMElement $seeLinkNode */
        $seeLinkNode = $this->_xpath->query("{$infNs}:seeLink", $appInfoNode)->item(0);
        $this->assertNotNull($seeLinkNode, sprintf('"seeLink" node was not found in "%s" element appinfo.',
            $elementName));
        foreach (array('url', 'title', 'for') as $subNodeName) {
            if (isset($appInfoData[$subNodeName])) {
                /** @var DOMElement $subNode */
                $subNodeValue = $appInfoData[$subNodeName];
                $subNode = $this->_xpath->query("{$infNs}:{$subNodeName}[text()='{$subNodeValue}']", $seeLinkNode)
                    ->item(0);
                $this->assertNotNull($subNode,
                    sprintf('"%s" node with value "%s" was not found in "%s" element appinfo "seeLink".',
                        $subNodeName, $subNodeValue, $elementName));
            }
        }
    }

    /**
     * Assert 'callInfo' annotation node and it's subnodes.
     *
     * @param DOMElement $appInfoNode
     * @param array $appInfoData
     */
    protected function _assertCallInfo(DOMElement $appInfoNode, array $appInfoData)
    {
        $infNs = Mage_Webapi_Model_Soap_Wsdl_ComplexTypeStrategy_ConfigBased::APP_INF_NS;
        $elementName = $appInfoNode->parentNode->parentNode->getAttribute('name');
        foreach ($appInfoData as $callName => $callData) {
            if ($callName == 'allCallsExcept') {
                /** @var DOMElement $callNode */
                $callNode = $this->_xpath
                    ->query("{$infNs}:callInfo/{$infNs}:allCallsExcept[text()='{$callData['calls']}']", $appInfoNode)
                    ->item(0);
                $this->assertNotNull($callNode,
                    sprintf('allCallsExcept node for call "%s" was not found in element "%s" appinfo.',
                        $callData['calls'], $elementName));
            } else {
                /** @var DOMElement $callNameNode */
                $callNode = $this->_xpath->query("{$infNs}:callInfo/{$infNs}:callName[text()='{$callName}']",
                    $appInfoNode)->item(0);
                $this->assertNotNull($callNode,
                    sprintf('callName node for call "%s" was not found in element "%s" appinfo.', $callName,
                        $elementName));
            }
            if (isset($callData['requiredInput'])) {
                $direction = 'requiredInput';
                $condition = $callData['requiredInput'];
            } else {
                $direction = 'returned';
                $condition = $callData['returned'];
            }
            $conditionNode = $this->_xpath->query("{$infNs}:{$direction}[text()='{$condition}']", $callNode->parentNode)
                ->item(0);
            $this->assertNotNull($conditionNode,
                sprintf('"%s" node with value "%s" not found for callName "%s" in element "%s"', $direction,
                    $condition, $callName, $elementName));
        }
    }

    /**
     * Assert that given complex type has correct documentation node.
     *
     * @param string $expectedDoc
     * @param DOMElement $element
     */
    protected function _assertDocumentation($expectedDoc, DOMElement $element)
    {
        $elementName = $element->getAttribute('name');
        $xsdNs = Wsdl::XSD_NS;
        /** @var DOMElement $documentation */
        $documentation = $this->_xpath->query("{$xsdNs}:annotation/{$xsdNs}:documentation", $element)->item(0);
        $this->assertNotNull($documentation,
            sprintf('"annotation/documentation" node was not found inside "%s" element.', $elementName));
        $this->assertEquals($expectedDoc, $documentation->nodeValue,
            sprintf('"documentation" node value is incorrect in "%s" element.', $elementName));
    }

    /**
     * Assert operation message (input/output) and that message node is present in WSDL
     *
     * @param DOMElement $operationMessage
     * @param $methodName
     * @param $expectedDoc
     * @return DOMElement
     */
    protected function _assertMessage(DOMElement $operationMessage, $methodName, $expectedDoc)
    {
        $wsdlNs = Wsdl::WSDL_NS;
        $tns = Wsdl::TYPES_NS;
        $xsdNs = Wsdl::XSD_NS;

        $this->assertTrue($operationMessage->hasAttribute('message'));
        $messageName = str_replace("{$tns}:", '', $operationMessage->getAttribute('message'));
        $this->assertEquals(Wsdl::TYPES_NS . ':' . $messageName,
            $operationMessage->getAttribute('message'));
        $messageTypeName = $this->_autoDiscover->getElementComplexTypeName($messageName);
        $complexTypeXpath = "//{$wsdlNs}:types/{$xsdNs}:schema/{$xsdNs}:complexType[@name='%s']";
        /** @var DOMElement $messageComplexType */
        $messageComplexType = $this->_xpath->query(sprintf($complexTypeXpath, $messageTypeName))->item(0);
        $this->assertNotNull($messageComplexType,
            sprintf('Complex type for "%s" operation was not found in WSDL.', $methodName));
        $this->_assertDocumentation($expectedDoc, $messageComplexType);

        /** @var DOMElement $message */
        $expression = "//{$wsdlNs}:message[@name='{$messageName}']";
        $message = $this->_xpath->query($expression)->item(0);
        $this->assertNotNull($message, sprintf('Message "%s" not found in WSDL.', $messageName));
        $partXpath = "{$wsdlNs}:part[@element='{$tns}:{$messageName}']";
        $messagePart = $this->_xpath->query($partXpath, $message)->item(0);
        $this->assertNotNull($messagePart, sprintf('Message part not found in "%s".', $messageName));

        return $messageComplexType;
    }

    /**
     * Assert operation is present in portType node and return it.
     *
     * @param $operationName
     * @param DOMElement $portType
     * @return DOMElement
     */
    protected function _assertPortTypeOperation($operationName, DOMElement $portType)
    {
        $operationXpath = sprintf('%s:operation[@name="%s"]', Wsdl::WSDL_NS, $operationName);
        /** @var DOMElement $portOperation */
        $portOperation = $this->_xpath->query($operationXpath, $portType)->item(0);
        $this->assertNotNull($portOperation, sprintf('Operation "%s" was not found in portType "%s".',
            $operationName, $this->_autoDiscover->getPortTypeName($this->_resourceName)));
        return $portOperation;
    }

    /**
     * Assert operation is present in binding node.
     *
     * @param string $operationName
     * @param DOMElement $binding
     */
    protected function _assertBindingOperation($operationName, DOMElement $binding)
    {
        $operationXpath = sprintf('%s:operation[@name="%s"]', Wsdl::WSDL_NS, $operationName);
        /** @var DOMElement $bindingOperation */
        $bindingOperation = $this->_xpath->query($operationXpath, $binding)->item(0);
        $this->assertNotNull($bindingOperation, sprintf('Operation "%s" was not found in binding "%s".',
            $operationName, $this->_autoDiscover->getBindingName($this->_resourceName)));
    }

    /**
     * Assert binding node is present and return it.
     *
     * @return DOMElement
     */
    protected function _assertBinding()
    {
        $bindings = $this->_dom->getElementsByTagNameNS(Wsdl::WSDL_NS_URI, 'binding');
        $this->assertEquals(1, $bindings->length, 'There should be only one binding in this test case.');
        /** @var DOMElement $binding */
        $binding = $bindings->item(0);
        $this->assertTrue($binding->hasAttribute('name'));
        $bindingName = $this->_autoDiscover->getBindingName($this->_resourceName);
        $this->assertEquals($bindingName, $binding->getAttribute('name'));
        $this->assertTrue($binding->hasAttribute('type'));
        $portTypeName = $this->_autoDiscover->getPortTypeName($this->_resourceName);
        $this->assertEquals(Wsdl::TYPES_NS . ':' . $portTypeName,
            $binding->getAttribute('type'));
        /** @var DOMElement $soapBinding */
        $soapBinding = $binding->getElementsByTagNameNS(Wsdl::SOAP_12_NS_URI, 'binding')
            ->item(0);
        $this->assertNotNull($soapBinding, sprintf('Missing soap binding in "%s"', $bindingName));
        $this->assertTrue($soapBinding->hasAttribute('style'));
        $this->assertEquals('document', $soapBinding->getAttribute('style'));

        return $binding;
    }

    /**
     * Assert port type node is present and return it.
     *
     * @return DOMElement
     */
    protected function _assertPortType()
    {
        $portTypes = $this->_dom->getElementsByTagNameNs(Wsdl::WSDL_NS_URI, 'portType');
        $this->assertEquals(1, $portTypes->length, 'There should be only one portType in this test case.');
        /** @var DOMElement $portType */
        $portType = $portTypes->item(0);
        $this->assertTrue($portType->hasAttribute('name'));
        $expectedName = $this->_autoDiscover->getPortTypeName($this->_resourceName);
        $this->assertEquals($expectedName, $portType->getAttribute('name'));

        return $portType;
    }

    /**
     * Assert port node is present within service node.
     *
     * @param DOMElement $service
     */
    protected function _assertPortNode($service)
    {
        /** @var DOMElement $port */
        $port = $service->getElementsByTagName('port')->item(0);
        $this->assertNotNull($port, 'port node not found within service node.');
        $this->assertTrue($port->hasAttribute('name'));
        $this->assertEquals($this->_autoDiscover->getPortName($this->_resourceName), $port->getAttribute('name'));
        $bindingName = $this->_autoDiscover->getBindingName($this->_resourceName);
        $this->assertEquals(Wsdl::TYPES_NS . ':' . $bindingName, $port->getAttribute('binding'));
    }

    /**
     * Assert service node is present in xml.
     *
     * @return DOMElement
     */
    protected function _assertServiceNode()
    {
        /** @var DOMElement $service */
        $service = $this->_dom->getElementsByTagNameNS(Wsdl::WSDL_NS_URI, 'service')->item(0);
        $this->assertNotNull($service, 'service node not found in WSDL.');
        $this->assertTrue($service->hasAttribute('name'));
        $this->assertEquals($this->_autoDiscover->getServiceName($this->_resourceName), $service->getAttribute('name'));

        $this->_assertPortNode($service, $this->_resourceName);
    }
}
