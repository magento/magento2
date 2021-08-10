<?php
/**
 * @see       https://github.com/laminas/laminas-soap for the canonical source repository
 * @copyright https://github.com/laminas/laminas-soap/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-soap/blob/master/LICENSE.md New BSD License
 */

namespace Magento\Webapi\Test\Unit\Model\Laminas\Soap;

use DOMDocument;
use Laminas\Uri\Uri;
use Magento\Webapi\Model\Laminas\Soap\ComplexTypeStrategy\AnyType;
use Magento\Webapi\Model\Laminas\Soap\ComplexTypeStrategy\ArrayOfTypeSequence;
use Magento\Webapi\Model\Laminas\Soap\ComplexTypeStrategy\DefaultComplexType;
use Magento\Webapi\Model\Laminas\Soap\Exception\RuntimeException;
use Magento\Webapi\Model\Laminas\Soap\Wsdl;
use Magento\Webapi\Test\Unit\Model\Laminas\Soap\TestAsset\WsdlTestClass;

class WsdlTest extends WsdlTestHelper
{
    public function testConstructor()
    {
        $this->assertEquals(Wsdl::WSDL_NS_URI, $this->dom->lookupNamespaceUri(null));
        $this->assertEquals(Wsdl::SOAP_11_NS_URI, $this->dom->lookupNamespaceUri('soap'));
        $this->assertEquals(Wsdl::SOAP_12_NS_URI, $this->dom->lookupNamespaceUri('soap12'));
        $this->assertEquals($this->defaultServiceUri, $this->dom->lookupNamespaceUri('tns'));
        $this->assertEquals(Wsdl::SOAP_11_NS_URI, $this->dom->lookupNamespaceUri('soap'));
        $this->assertEquals(Wsdl::XSD_NS_URI, $this->dom->lookupNamespaceUri('xsd'));
        $this->assertEquals(Wsdl::SOAP_ENC_URI, $this->dom->lookupNamespaceUri('soap-enc'));
        $this->assertEquals(Wsdl::WSDL_NS_URI, $this->dom->lookupNamespaceUri('wsdl'));

        $this->assertEquals(Wsdl::WSDL_NS_URI, $this->dom->documentElement->namespaceURI);

        $this->assertEquals($this->defaultServiceName, $this->dom->documentElement->getAttribute('name'));
        $this->assertEquals($this->defaultServiceUri, $this->dom->documentElement->getAttribute('targetNamespace'));

        $this->documentNodesTest();
    }

    /**
     * @dataProvider dataProviderForURITesting
     *
     * @param string $uri
     */
    public function testSetUriChangesDomDocumentWsdlStructureTnsAndTargetNamespaceAttributes($uri, $expectedUri)
    {
        if ($uri instanceof Uri) {
            $uri = $uri->toString();
        }

        $this->wsdl->setUri($uri);

        $this->documentNodesTest();

        $this->assertEquals($expectedUri, $this->dom->lookupNamespaceUri('tns'));
        $this->assertEquals($expectedUri, $this->dom->documentElement->getAttribute('targetNamespace'));
    }

    /**
     * @dataProvider dataProviderForURITesting
     *
     * @param string $uri
     */
    public function testSetUriWithLaminasUriChangesDomDocumentWsdlStructureTnsAndTargetNamespaceAttributes(
        $uri,
        $expectedUri
    ) {
        $this->wsdl->setUri(new Uri($uri));
        $this->documentNodesTest();

        $this->assertEquals($expectedUri, $this->dom->lookupNamespaceUri('tns'));
        $this->assertEquals($expectedUri, $this->dom->documentElement->getAttribute('targetNamespace'));
    }

    /**
     * @dataProvider dataProviderForURITesting
     *
     * @param string $uri
     */
    public function testObjectConstructionWithDifferentURI($uri, $expectedUri)
    {
        $wsdl = new Wsdl($this->defaultServiceName, $uri);

        $dom = $this->registerNamespaces($wsdl->toDomDocument(), $uri);
        $this->documentNodesTest();

        $this->assertEquals($expectedUri, $dom->lookupNamespaceUri('tns'));
        $this->assertEquals($expectedUri, $dom->documentElement->getAttribute('targetNamespace'));
    }

    /**
     * Data provider for uri testing
     *
     * @return array
     */
    public function dataProviderForURITesting()
    {
        return [
            ['http://localhost/MyService.php',                 'http://localhost/MyService.php'],
            ['http://localhost/MyNewService.php',              'http://localhost/MyNewService.php'],
            [new Uri('http://localhost/MyService.php'),        'http://localhost/MyService.php'],
            /**
             * @bug Laminas-5736
             */
            ['http://localhost/MyService.php?a=b&amp;b=c',     'http://localhost/MyService.php?a=b&amp;b=c'],

            /**
             * @bug Laminas-5736
             */
            ['http://localhost/MyService.php?a=b&b=c',         'http://localhost/MyService.php?a=b&amp;b=c'],
        ];
    }

    /**
     * @dataProvider dataProviderForAddMessage
     *
     * @param array $parameters message parameters
     */
    public function testAddMessage($parameters)
    {
        $messageParts = [];
        foreach ($parameters as $i => $parameter) {
            $messageParts['parameter'.$i] = $this->wsdl->getType($parameter);
        }

        $messageName = 'myMessage';

        $this->wsdl->addMessage($messageName, $messageParts);
        $this->documentNodesTest();

        $messageNodes = $this->xpath->query('//wsdl:definitions/wsdl:message');

        $this->assertGreaterThan(0, $messageNodes->length, 'Missing message node in definitions node.');

        $this->assertEquals($messageName, $messageNodes->item(0)->getAttribute('name'));

        foreach ($messageParts as $parameterName => $parameterType) {
            $part = $this->xpath->query('wsdl:part[@name="'.$parameterName.'"]', $messageNodes->item(0));
            $this->assertEquals($parameterType, $part->item(0)->getAttribute('type'));
        }
    }

    /**
     * @dataProvider dataProviderForAddMessage
     *
     * @param array $parameters complex message parameters
     */
    public function testAddComplexMessage($parameters)
    {
        $messageParts = [];
        foreach ($parameters as $i => $parameter) {
            $messageParts['parameter'.$i] = [
                'type'      => $this->wsdl->getType($parameter),
                'name'      => 'parameter'.$i
            ];
        }

        $messageName = 'myMessage';

        $this->wsdl->addMessage($messageName, $messageParts);
        $this->documentNodesTest();

        $messageNodes = $this->xpath->query('//wsdl:definitions/wsdl:message');

        $this->assertGreaterThan(0, $messageNodes->length, 'Missing message node in definitions node.');

        foreach ($messageParts as $parameterName => $parameterDefinition) {
            $part = $this->xpath->query('wsdl:part[@name="'.$parameterName.'"]', $messageNodes->item(0));
            $this->assertEquals($parameterDefinition['type'], $part->item(0)->getAttribute('type'));
            $this->assertEquals($parameterDefinition['name'], $part->item(0)->getAttribute('name'));
        }
    }

    /**
     * @return array
     */
    public function dataProviderForAddMessage()
    {
        return [
            [['int', 'int', 'int']],
            [['string', 'string', 'string', 'string']],
            [['mixed']],
            [['int', 'int', 'string', 'string']],
            [['int', 'string', 'int', 'string']],
        ];
    }

    public function testAddPortType()
    {
        $portName = 'myPortType';
        $this->wsdl->addPortType($portName);

        $this->documentNodesTest();

        $portTypeNodes = $this->xpath->query('//wsdl:definitions/wsdl:portType');

        $this->assertGreaterThan(0, $portTypeNodes->length, 'Missing portType node in definitions node.');

        $this->assertTrue($portTypeNodes->item(0)->hasAttribute('name'));
        $this->assertEquals($portName, $portTypeNodes->item(0)->getAttribute('name'));
    }

    /**
     * @dataProvider dataProviderForAddPortOperation
     *
     * @param string $operationName
     */
    public function testAddPortOperation($operationName, $inputRequest = null, $outputResponse = null, $fail = null)
    {
        $portName = 'myPortType';
        $portType = $this->wsdl->addPortType($portName);

        $this->wsdl->addPortOperation($portType, $operationName, $inputRequest, $outputResponse, $fail);

        $this->documentNodesTest();

        $portTypeNodes = $this->xpath->query('//wsdl:definitions/wsdl:portType[@name="'.$portName.'"]');
        $this->assertGreaterThan(0, $portTypeNodes->length, 'Missing portType node in definitions node.');

        $operationNodes = $this->xpath->query('wsdl:operation[@name="'.$operationName.'"]', $portTypeNodes->item(0));
        $this->assertGreaterThan(0, $operationNodes->length);

        if (empty($inputRequest) and empty($outputResponse) and empty($fail)) {
            $this->assertFalse($operationNodes->item(0)->hasChildNodes());
        } else {
            $this->assertTrue($operationNodes->item(0)->hasChildNodes());
        }

        if (! empty($inputRequest)) {
            $inputNodes = $operationNodes->item(0)->getElementsByTagName('input');
            $this->assertEquals($inputRequest, $inputNodes->item(0)->getAttribute('message'));
        }

        if (! empty($outputResponse)) {
            $outputNodes = $operationNodes->item(0)->getElementsByTagName('output');
            $this->assertEquals($outputResponse, $outputNodes->item(0)->getAttribute('message'));
        }

        if (! empty($fail)) {
            $faultNodes = $operationNodes->item(0)->getElementsByTagName('fault');
            $this->assertEquals($fail, $faultNodes->item(0)->getAttribute('message'));
        }
    }

    public function dataProviderForAddPortOperation()
    {
        return [
            ['operation'],
            ['operation', 'tns:operationRequest', 'tns:operationResponse'],
            ['operation', 'tns:operationRequest', 'tns:operationResponse', 'tns:operationFault'],
            ['operation', 'tns:operationRequest', null, 'tns:operationFault'],
            ['operation', null, null, 'tns:operationFault'],
            ['operation', null, 'tns:operationResponse', 'tns:operationFault'],
            ['operation', null, 'tns:operationResponse'],
        ];
    }

    public function testAddBinding()
    {
        $this->wsdl->addBinding('MyServiceBinding', 'myPortType');

        $this->documentNodesTest();

        $bindingNodes = $this->xpath->query('//wsdl:definitions/wsdl:binding');

        if ($bindingNodes->length === 0) {
            $this->fail('Missing binding node in definitions node.'.$bindingNodes->length);
        }

        $this->assertEquals('MyServiceBinding', $bindingNodes->item(0)->getAttribute('name'));
        $this->assertEquals('myPortType', $bindingNodes->item(0)->getAttribute('type'));
    }

    /**
     * @dataProvider dataProviderForAddBindingOperation
     *
     * @param $operationName
     * @param null $input
     * @param null $inputEncoding
     * @param null $output
     * @param null $outputEncoding
     * @param null $fault
     * @param null $faultEncoding
     * @param null $faultName
     */
    public function testAddBindingOperation(
        $operationName,
        $input = null,
        $inputEncoding = null,
        $output = null,
        $outputEncoding = null,
        $fault = null,
        $faultEncoding = null,
        $faultName = null
    ) {

        $binding = $this->wsdl->addBinding('MyServiceBinding', 'myPortType');

        $inputArray = [];
        if (! empty($input) and ! empty($inputEncoding)) {
            $inputArray = ['use' => $input,     'encodingStyle' => $inputEncoding];
        }

        $outputArray = [];
        if (! empty($output) and ! empty($outputEncoding)) {
            $outputArray = ['use' => $output, 'encodingStyle' => $outputEncoding];
        }

        $faultArray = [];
        if (! empty($fault) and ! empty($faultEncoding) and ! empty($faultName)) {
            $faultArray = ['use' => $fault,     'encodingStyle' => $faultEncoding,     'name' => $faultName];
        }

        $this->wsdl->addBindingOperation(
            $binding,
            $operationName,
            $inputArray,
            $outputArray,
            $faultArray
        );

        $this->documentNodesTest();

        $bindingNodes = $this->xpath->query('//wsdl:binding');

        $this->assertGreaterThan(0, $bindingNodes->length, 'Missing binding node in definition.');

        $this->assertEquals('MyServiceBinding', $bindingNodes->item(0)->getAttribute('name'));
        $this->assertEquals('myPortType', $bindingNodes->item(0)->getAttribute('type'));

        $operationNodes = $this->xpath->query('wsdl:operation[@name="'.$operationName.'"]', $bindingNodes->item(0));
        $this->assertEquals(1, $operationNodes->length, 'Missing operation node in definition.');

        if (empty($inputArray) and empty($outputArray) and empty($faultArray)) {
            $this->assertFalse($operationNodes->item(0)->hasChildNodes());
        }

        foreach ([
                     '//wsdl:input/soap:body'    => $inputArray,
                     '//wsdl:output/soap:body'   => $outputArray,
                     '//wsdl:fault'              => $faultArray
                 ] as $query => $ar) {
            if (! empty($ar)) {
                $nodes = $this->xpath->query($query);

                $this->assertGreaterThan(0, $nodes->length, 'Missing operation body.');

                foreach ($ar as $key => $val) {
                    $this->assertEquals(
                        $ar[$key],
                        $nodes->item(0)->getAttribute($key),
                        'Bad attribute in operation definition: '.$key
                    );
                }
            }
        }
    }

    public function dataProviderForAddBindingOperation()
    {
        $enc = 'http://schemas.xmlsoap.org/soap/encoding/';

        return [
            ['operation'],
            ['operation', 'encoded', $enc, 'encoded', $enc, 'encoded', $enc, 'myFaultName'],
            ['operation', null, null, 'encoded', $enc, 'encoded', $enc, 'myFaultName'],
            ['operation', null, null, 'encoded', $enc, 'encoded'],
            ['operation', 'encoded', $enc],
            ['operation', null, null, null, null, 'encoded', $enc, 'myFaultName'],
            ['operation', 'encoded1', $enc.'1', 'encoded2', $enc.'2', 'encoded3', $enc.'3', 'myFaultName'],

        ];
    }

    /**
     * @dataProvider dataProviderForSoapBindingStyle
     */
    public function testAddSoapBinding($style)
    {
        $this->wsdl->addPortType('myPortType');
        $binding = $this->wsdl->addBinding('MyServiceBinding', 'myPortType');

        $this->wsdl->addSoapBinding($binding, $style);

        $this->documentNodesTest();

        $nodes = $this->xpath->query('//soap:binding');

        $this->assertGreaterThan(0, $nodes->length);
        $this->assertEquals($style, $nodes->item(0)->getAttribute('style'));
    }

    public function dataProviderForSoapBindingStyle()
    {
        return [
            ['document'],
            ['rpc']
        ];
    }

    /**
     * @dataProvider dataProviderForAddSoapOperation
     */
    public function testAddSoapOperation($operationUrl)
    {
        $this->wsdl->addPortType('myPortType');
        $binding = $this->wsdl->addBinding('MyServiceBinding', 'myPortType');

        $this->wsdl->addSoapOperation($binding, $operationUrl);

        $this->documentNodesTest();

        $node = $this->xpath->query('//soap:operation');
        $this->assertGreaterThan(0, $node->length);
        $this->assertEquals($operationUrl, $node->item(0)->getAttribute('soapAction'));
    }

    public function dataProviderForAddSoapOperation()
    {
        return [
            ['http://localhost/MyService.php#myOperation'],
            [new Uri('http://localhost/MyService.php#myOperation')]
        ];
    }

    /**
     * @dataProvider dataProviderForAddService
     */
    public function testAddService($serviceUrl)
    {
        $this->wsdl->addPortType('myPortType');
        $this->wsdl->addBinding('MyServiceBinding', 'myPortType');

        $this->wsdl->addService('Service1', 'myPortType', 'MyServiceBinding', $serviceUrl);

        $this->documentNodesTest();

        $nodes = $this->xpath->query('//wsdl:service[@name="Service1"]/wsdl:port/soap:address');
        $this->assertGreaterThan(0, $nodes->length);

        $this->assertEquals($serviceUrl, $nodes->item(0)->getAttribute('location'));
    }

    /**
     * @return array
     */
    public function dataProviderForAddService()
    {
        return [
            ['http://localhost/MyService.php'],
            [new Uri('http://localhost/MyService.php')]
        ];
    }

    /**
     * @dataProvider ampersandInUrlDataProvider
     */
    public function testAddBindingOperationWithAmpersandInUrl($actualUrl, $expectedUrl)
    {
        $this->wsdl->addPortType('myPortType');
        $binding = $this->wsdl->addBinding('MyServiceBinding', 'myPortType');

        $this->wsdl->addBindingOperation(
            $binding,
            'operation1',
            ['use' => 'encoded', 'encodingStyle' => $actualUrl],
            ['use' => 'encoded', 'encodingStyle' => $actualUrl],
            ['name' => 'MyFault', 'use' => 'encoded', 'encodingStyle' => $actualUrl]
        );

        $nodes = $this->xpath->query(
            '//wsdl:binding[@type="myPortType" '
            . 'and @name="MyServiceBinding"]/wsdl:operation[@name="operation1"]/wsdl:input/soap:body'
        );

        $this->assertGreaterThanOrEqual(1, $nodes->length);
        $this->assertEquals($expectedUrl, $nodes->item(0)->getAttribute('encodingStyle'));
    }

    /**
     * @dataProvider ampersandInUrlDataProvider()
     */
    public function testAddSoapOperationWithAmpersandInUrl($actualUrl, $expectedUrl)
    {
        $this->wsdl->addPortType('myPortType');
        $binding = $this->wsdl->addBinding('MyServiceBinding', 'myPortType');

        $this->wsdl->addSoapOperation($binding, $actualUrl);

        $this->documentNodesTest();

        $nodes = $this->xpath->query('//wsdl:binding/soap:operation');
        $this->assertGreaterThanOrEqual(1, $nodes->length);
        $this->assertEquals($expectedUrl, $nodes->item(0)->getAttribute('soapAction'));
    }

    /**
     * @dataProvider ampersandInUrlDataProvider()
     */
    public function testAddServiceWithAmpersandInUrl($actualUrl, $expectedUrl)
    {
        $this->wsdl->addPortType('myPortType');
        $this->wsdl->addBinding('MyServiceBinding', 'myPortType');

        $this->wsdl->addService('Service1', 'myPortType', 'MyServiceBinding', $actualUrl);

        $this->documentNodesTest();

        $nodes = $this->xpath->query('//wsdl:port/soap:address');
        $this->assertGreaterThanOrEqual(1, $nodes->length);
        $this->assertEquals($expectedUrl, $nodes->item(0)->getAttribute('location'));
    }

    public function ampersandInUrlDataProvider()
    {
        return [
            'Decoded ampersand' => [
                'http://localhost/MyService.php?foo=bar&baz=qux',
                'http://localhost/MyService.php?foo=bar&amp;baz=qux'
            ],
            'Encoded ampersand' => [
                'http://localhost/MyService.php?foo=bar&amp;baz=qux',
                'http://localhost/MyService.php?foo=bar&amp;baz=qux'
            ],
            'Encoded and decoded ampersand' => [
                'http://localhost/MyService.php?foo=bar&&amp;baz=qux',
                'http://localhost/MyService.php?foo=bar&amp;&amp;baz=qux'
            ]
        ];
    }

    public function testAddDocumentation()
    {
        $doc = 'This is a description for Port Type node.';
        $this->wsdl->addDocumentation($this->wsdl, $doc);

        $this->documentNodesTest();

        $nodes = $this->wsdl->toDomDocument()->childNodes;
        $this->assertEquals(1, $nodes->length);
        $this->assertEquals($doc, $nodes->item(0)->nodeValue);
    }

    public function testAddDocumentationToSomeElmenet()
    {
        $portType = $this->wsdl->addPortType('myPortType');

        $doc = 'This is a description for Port Type node.';
        $this->wsdl->addDocumentation($portType, $doc);

        $this->documentNodesTest();

        $nodes = $this->xpath->query('//wsdl:portType[@name="myPortType"]/wsdl:documentation');
        $this->assertEquals(1, $nodes->length);
        $this->assertEquals($doc, $nodes->item(0)->nodeValue);
    }

    public function testAddDocumentationToSetInsertsBefore()
    {
        $messageParts = [];
        $messageParts['parameter1'] = $this->wsdl->getType('int');
        $messageParts['parameter2'] = $this->wsdl->getType('string');
        $messageParts['parameter3'] = $this->wsdl->getType('mixed');

        $message = $this->wsdl->addMessage('myMessage', $messageParts);
        $this->wsdl->addDocumentation($message, "foo");

        $this->documentNodesTest();

        $nodes = $this->xpath->query('//wsdl:message[@name="myMessage"]/*[1]');
        $this->assertEquals('documentation', $nodes->item(0)->nodeName);
    }

    public function testComplexTypeDocumentationAddedAsAnnotation()
    {
        $this->wsdl->addComplexType(WsdlTestClass::class);
        $nodes = $this->xpath->query('//xsd:complexType[@name="WsdlTestClass"]');

        $this->wsdl->addDocumentation($nodes->item(0), 'documentation');

        $nodes = $this->xpath->query('//xsd:complexType[@name="WsdlTestClass"]/*[1]');
        $this->assertEquals('xsd:annotation', $nodes->item(0)->nodeName);

        $nodes = $this->xpath->query('//xsd:complexType[@name="WsdlTestClass"]/xsd:annotation/*[1]');
        $this->assertEquals('xsd:documentation', $nodes->item(0)->nodeName);
    }

    public function testDumpToFile()
    {
        $file = tempnam(sys_get_temp_dir(), 'laminasunittest');

        $dumpStatus = $this->wsdl->dump($file);

        $fileContent = file_get_contents($file);
        unlink($file);

        $this->assertTrue($dumpStatus, 'WSDL Dump fail');

        $this->checkXMLContent($fileContent);
    }

    public function testDumpToOutput()
    {
        ob_start();
        $dumpStatus = $this->wsdl->dump();
        $screenContent = ob_get_clean();

        $this->assertTrue($dumpStatus, 'Dump to output failed');

        $this->checkXMLContent($screenContent);
    }

    public function checkXMLContent($content)
    {
        libxml_use_internal_errors(true);
        if (LIBXML_VERSION < 20900) {
            libxml_disable_entity_loader(false);
        }
        $xml = new DOMDocument();
        $xml->preserveWhiteSpace = false;
        $xml->encoding = 'UTF-8';
        $xml->formatOutput = false;
        $xml->loadXML($content);

        $errors = libxml_get_errors();
        $this->assertEmpty($errors, 'Libxml parsing errors: '.print_r($errors, 1));

        $this->dom = $this->registerNamespaces($xml);

        $this->testConstructor();

        $this->documentNodesTest();
    }

    public function testGetType()
    {
        $this->assertEquals('xsd:string', $this->wsdl->getType('string'), 'xsd:string detection failed.');
        $this->assertEquals('xsd:string', $this->wsdl->getType('str'), 'xsd:string detection failed.');
        $this->assertEquals('xsd:int', $this->wsdl->getType('int'), 'xsd:int detection failed.');
        $this->assertEquals('xsd:int', $this->wsdl->getType('integer'), 'xsd:int detection failed.');
        $this->assertEquals('xsd:float', $this->wsdl->getType('float'), 'xsd:float detection failed.');
        $this->assertEquals('xsd:double', $this->wsdl->getType('double'), 'xsd:double detection failed.');
        $this->assertEquals('xsd:boolean', $this->wsdl->getType('boolean'), 'xsd:boolean detection failed.');
        $this->assertEquals('xsd:boolean', $this->wsdl->getType('bool'), 'xsd:boolean detection failed.');
        $this->assertEquals('soap-enc:Array', $this->wsdl->getType('array'), 'soap-enc:Array detection failed.');
        $this->assertEquals('xsd:struct', $this->wsdl->getType('object'), 'xsd:struct detection failed.');
        $this->assertEquals('xsd:anyType', $this->wsdl->getType('mixed'), 'xsd:anyType detection failed.');
        $this->assertEquals('xsd:date', $this->wsdl->getType('date'), 'xsd:date detection failed.');
        $this->assertEquals('xsd:dateTime', $this->wsdl->getType('datetime'), 'xsd:dateTime detection failed.');
        $this->assertEquals('', $this->wsdl->getType('void'), 'void  detection failed.');
    }

    public function testGetComplexTypeBasedOnStrategiesBackwardsCompabilityBoolean()
    {
        $this->assertEquals('tns:WsdlTestClass', $this->wsdl->getType(WsdlTestClass::class));
        $this->assertInstanceOf(
            'Magento\Webapi\Model\Laminas\Soap\ComplexTypeStrategy\DefaultComplexType',
            $this->wsdl->getComplexTypeStrategy()
        );
    }

    public function testGetComplexTypeBasedOnStrategiesStringNames()
    {
        $this->wsdl = new Wsdl(
            $this->defaultServiceName,
            'http://localhost/MyService.php',
            new DefaultComplexType
        );
        $this->assertEquals('tns:WsdlTestClass', $this->wsdl->getType(WsdlTestClass::class));
        $this->assertInstanceOf(
            'Magento\Webapi\Model\Laminas\Soap\ComplexTypeStrategy\DefaultComplexType',
            $this->wsdl->getComplexTypeStrategy()
        );

        $wsdl2 = new Wsdl($this->defaultServiceName, $this->defaultServiceUri, new AnyType());
        $this->assertEquals('xsd:anyType', $wsdl2->getType(WsdlTestClass::class));
        $this->assertInstanceOf('Magento\Webapi\Model\Laminas\Soap\ComplexTypeStrategy\AnyType', $wsdl2->getComplexTypeStrategy());
    }

    public function testAddingSameComplexTypeMoreThanOnceIsIgnored()
    {
        $this->wsdl->addType(WsdlTestClass::class, 'tns:SomeTypeName');
        $this->wsdl->addType(WsdlTestClass::class, 'tns:AnotherTypeName');
        $types = $this->wsdl->getTypes();
        $this->assertEquals(1, count($types));
        $this->assertEquals(
            [
                WsdlTestClass::class => 'tns:SomeTypeName'
            ],
            $types
        );

        $this->documentNodesTest();
    }

    public function testUsingSameComplexTypeTwiceLeadsToReuseOfDefinition()
    {
        $this->wsdl->addComplexType(WsdlTestClass::class);
        $this->assertEquals(
            [
                WsdlTestClass::class => 'tns:WsdlTestClass'
            ],
            $this->wsdl->getTypes()
        );

        $this->wsdl->addComplexType(WsdlTestClass::class);
        $this->assertEquals(
            [
                WsdlTestClass::class => 'tns:WsdlTestClass'
            ],
            $this->wsdl->getTypes()
        );

        $this->documentNodesTest();
    }

    public function testGetSchema()
    {
        $schema = $this->wsdl->getSchema();

        $this->assertEquals($this->defaultServiceUri, $schema->getAttribute('targetNamespace'));
    }

    public function testAddComplexType()
    {
        $this->wsdl->addComplexType(WsdlTestClass::class);

        $this->documentNodesTest();

        $nodes = $this->xpath->query('//wsdl:types/xsd:schema/xsd:complexType/xsd:all/*');

        $this->assertGreaterThan(0, $nodes->length, 'Unable to find object properties in wsdl');
    }

    public function testAddTypesFromDocument()
    {
        $dom = new DOMDocument();
        $types = $dom->createElementNS(WSDL::WSDL_NS_URI, 'types');
        $dom->appendChild($types);

        $this->wsdl->addTypes($dom);

        $nodes = $this->xpath->query('//wsdl:types');
        $this->assertGreaterThanOrEqual(1, $nodes->length);

        $this->documentNodesTest();
    }

    public function testAddTypesFromNode()
    {
        $dom = $this->dom->createElementNS(WSDL::WSDL_NS_URI, 'types');

        $this->wsdl->addTypes($dom);

        $nodes = $this->xpath->query('//wsdl:types');
        $this->assertGreaterThanOrEqual(1, $nodes->length);

        $this->documentNodesTest();
    }

    public function testTranslateTypeFromClassMap()
    {
        $this->wsdl->setClassMap([
            'SomeType' => 'SomeOtherType'
        ]);

        $this->assertEquals('SomeOtherType', $this->wsdl->translateType('SomeType'));
    }

    /**
     * @dataProvider dataProviderForTranslateType
     */
    public function testTranslateType($type, $expected)
    {
        $this->assertEquals($expected, $this->wsdl->translateType($type));
    }

    /**
     * @return array
     */
    public function dataProviderForTranslateType()
    {
        return [
            ['\\SomeType','SomeType'],
            ['SomeType\\','SomeType'],
            ['\\SomeType\\','SomeType'],
            ['\\SomeNamespace\SomeType\\','SomeType'],
            ['\\SomeNamespace\SomeType\\SomeOtherType','SomeOtherType'],
            ['\\SomeNamespace\SomeType\\SomeOtherType\\YetAnotherType','YetAnotherType'],
        ];
    }


    /**
     * @group Laminas-3910
     * @group Laminas-11937
     */
    public function testCaseOfDocBlockParamsDosNotMatterForSoapTypeDetectionLaminas3910()
    {
        $this->assertEquals("xsd:string", $this->wsdl->getType("StrIng"));
        $this->assertEquals("xsd:string", $this->wsdl->getType("sTr"));
        $this->assertEquals("xsd:int", $this->wsdl->getType("iNt"));
        $this->assertEquals("xsd:int", $this->wsdl->getType("INTEGER"));
        $this->assertEquals("xsd:float", $this->wsdl->getType("FLOAT"));
        $this->assertEquals("xsd:double", $this->wsdl->getType("douBLE"));
        $this->assertEquals("xsd:date", $this->wsdl->getType("daTe"));

        $this->assertEquals("xsd:long", $this->wsdl->getType("long"));
    }

    /**
     * @group Laminas-5430
     */
    public function testMultipleSequenceDefinitionsOfSameTypeWillBeRecognizedOnceBySequenceStrategy()
    {
        $this->wsdl->setComplexTypeStrategy(new ArrayOfTypeSequence());

        $this->wsdl->addComplexType("string[]");
        $this->wsdl->addComplexType("int[]");
        $this->wsdl->addComplexType("string[]");
        $this->wsdl->addComplexType("int[]");

        $this->documentNodesTest();

        $nodes = $this->xpath->query('//wsdl:types/xsd:schema/xsd:complexType[@name="ArrayOfString"]');
        $this->assertEquals(1, $nodes->length, "ArrayOfString should appear only once.");

        $nodes = $this->xpath->query('//wsdl:types/xsd:schema/xsd:complexType[@name="ArrayOfInt"]');
        $this->assertEquals(1, $nodes->length, "ArrayOfInt should appear only once.");
    }

    public function testClassMap()
    {
        $this->wsdl->setClassMap(['foo' => 'bar']);

        $this->assertArrayHasKey('foo', $this->wsdl->getClassMap());
    }

    public function testAddElementException()
    {
        $this->expectException(RuntimeException::class);
        $this->wsdl->addElement(1);
    }

    public function testAddElement()
    {
        $element = [
            'name'      => 'MyElement',
            'sequence'  => [
                ['name' => 'myString', 'type' => 'string'],
                ['name' => 'myInt',    'type' => 'int']
            ]
        ];

        $newElementName = $this->wsdl->addElement($element);

        $this->documentNodesTest();

        $this->assertEquals('tns:'.$element['name'], $newElementName);

        $nodes = $this->xpath->query(
            '//wsdl:types/xsd:schema/xsd:element[@name="' . $element['name'] . '"]/xsd:complexType'
        );

        $this->assertEquals(1, $nodes->length);

        $this->assertEquals('sequence', $nodes->item(0)->firstChild->localName);

        $n = 0;
        foreach ($element['sequence'] as $elementDefinition) {
            $n++;
            $elementNode = $this->xpath->query(
                'xsd:element[@name="' . $elementDefinition['name'] . '"]',
                $nodes->item(0)->firstChild
            );
            $this->assertEquals($elementDefinition['type'], $elementNode->item(0)->getAttribute('type'));
        }

        $this->assertEquals(count($element['sequence']), $n);
    }
}
