<?php
/**
 * @see       https://github.com/laminas/laminas-soap for the canonical source repository
 * @copyright https://github.com/laminas/laminas-soap/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-soap/blob/master/LICENSE.md New BSD License
 */

namespace Magento\Webapi\Test\Unit\Model\Laminas\Soap;

use DOMDocument;
use Exception;
use Laminas\Config\Config;
use Magento\Webapi\Model\Laminas\Soap\AutoDiscover;
use Magento\Webapi\Model\Laminas\Soap\Client\Local;
use Magento\Webapi\Model\Laminas\Soap\Exception\InvalidArgumentException;
use Magento\Webapi\Model\Laminas\Soap\Exception\RuntimeException;
use Magento\Webapi\Model\Laminas\Soap\Server;
use Magento\Webapi\Test\Unit\Model\Laminas\Soap\TestAsset;
use Magento\Webapi\Test\Unit\Model\Laminas\Soap\TestAsset\MockServer;
use Magento\Webapi\Test\Unit\Model\Laminas\Soap\TestAsset\ServerTestClass;
use Magento\Webapi\Test\Unit\Model\Laminas\Soap\TestAsset\TestLocalSoapClient;
use PHPUnit\Framework\TestCase;
use SoapFault;

class ServerTest extends TestCase
{
    public function setUp(): void
    {
        if (! extension_loaded('soap')) {
            $this->markTestSkipped('SOAP Extension is not loaded');
        }
    }

    public function testSetOptions()
    {
        $server = new Server();

        $this->assertEquals(['soap_version' => SOAP_1_2], $server->getOptions());

        $options = ['soap_version' => SOAP_1_1,
            'actor' => 'https://getlaminas.org/Laminas_Soap_ServerTest.php',
            'classmap'       => [
                'TestData1' => TestAsset\TestData1::class,
                'TestData2' => TestAsset\TestData2::class
            ],
            'encoding' => 'ISO-8859-1',
            'uri' => 'https://getlaminas.org/Laminas_Soap_ServerTest.php',
            'parse_huge' => false
        ];
        $server->setOptions($options);

        $this->assertEquals($options, $server->getOptions());
    }

    public function testSetOptionsViaSecondConstructorArgument()
    {
        $options = [
            'soap_version' => SOAP_1_1,
            'actor' => 'https://getlaminas.org/Laminas_Soap_ServerTest.php',
            'classmap'       => [
                'TestData1' => TestAsset\TestData1::class,
                'TestData2' => TestAsset\TestData2::class
            ],
            'encoding' => 'ISO-8859-1',
            'uri' => 'https://getlaminas.org/Laminas_Soap_ServerTest.php',
            'parse_huge' => false
        ];
        $server = new Server(null, $options);

        $this->assertEquals($options, $server->getOptions());
    }

    /**
     * @group Laminas-9816
     */
    public function testSetOptionsWithFeaturesOption()
    {
        $server = new Server(null, [
            'features' => SOAP_SINGLE_ELEMENT_ARRAYS
        ]);

        $this->assertEquals(
            SOAP_SINGLE_ELEMENT_ARRAYS,
            $server->getSoapFeatures()
        );
    }

    public function testSetWsdlViaOptionsArrayIsPossible()
    {
        $server = new Server();
        $server->setOptions(['wsdl' => 'http://www.example.com/test.wsdl']);

        $this->assertEquals('http://www.example.com/test.wsdl', $server->getWSDL());
    }

    public function testGetOptions()
    {
        $server = new Server();

        $this->assertEquals(['soap_version' => SOAP_1_2], $server->getOptions());

        $options = ['soap_version' => SOAP_1_1,
            'uri' => 'https://getlaminas.org/Laminas_Soap_ServerTest.php'
        ];
        $server->setOptions($options);

        $this->assertEquals($options, $server->getOptions());
    }

    public function testEncoding()
    {
        $server = new Server();

        $this->assertNull($server->getEncoding());
        $server->setEncoding('ISO-8859-1');
        $this->assertEquals('ISO-8859-1', $server->getEncoding());

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid encoding specified');
        $server->setEncoding(['UTF-8']);
    }

    public function testSoapVersion()
    {
        $server = new Server();

        $this->assertEquals(SOAP_1_2, $server->getSoapVersion());
        $server->setSoapVersion(SOAP_1_1);
        $this->assertEquals(SOAP_1_1, $server->getSoapVersion());

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid soap version specified');
        $server->setSoapVersion('bogus');
    }

    public function testValidateUrn()
    {
        $server = new Server();
        $this->assertTrue($server->validateUrn('https://getlaminas.org/'));
        $this->assertTrue($server->validateUrn('urn:soapHandler/GetOpt'));

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid URN');
        $server->validateUrn('bogosity');
    }

    public function testSetActor()
    {
        $server = new Server();

        $this->assertNull($server->getActor());
        $server->setActor('https://getlaminas.org/');
        $this->assertEquals('https://getlaminas.org/', $server->getActor());

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid URN');
        $server->setActor('bogus');
    }

    public function testGetActor()
    {
        $server = new Server();

        $this->assertNull($server->getActor());
        $server->setActor('https://getlaminas.org/');
        $this->assertEquals('https://getlaminas.org/', $server->getActor());
    }

    public function testSetUri()
    {
        $server = new Server();

        $this->assertNull($server->getUri());
        $server->setUri('https://getlaminas.org/');
        $this->assertEquals('https://getlaminas.org/', $server->getUri());

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid URN');
        $server->setUri('bogus');
    }

    public function testGetUri()
    {
        $server = new Server();

        $this->assertNull($server->getUri());
        $server->setUri('https://getlaminas.org/');
        $this->assertEquals('https://getlaminas.org/', $server->getUri());
    }

    public function testSetClassmap()
    {
        $server = new Server();

        $classmap = [
                'TestData1' => TestAsset\TestData1::class,
                'TestData2' => TestAsset\TestData2::class
        ];

        $this->assertNull($server->getClassmap());
        $server->setClassmap($classmap);
        $this->assertSame($classmap, $server->getClassmap());
    }

    public function testSetClassmapThrowsExceptionOnBogusStringParameter()
    {
        $server = new Server();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Classmap must be an array');
        $server->setClassmap('bogus');
    }

    public function testSetClassmapThrowsExceptionOnBogusArrayParameter()
    {
        $server = new Server();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid class in class map');
        $server->setClassmap(['soapTypeName', 'bogusClassName']);
    }

    public function testGetClassmap()
    {
        $server = new Server();

        $classmap = [
            'TestData1' => TestAsset\TestData1::class,
            'TestData2' => TestAsset\TestData2::class
        ];

        $this->assertNull($server->getClassmap());
        $server->setClassmap($classmap);
        $this->assertSame($classmap, $server->getClassmap());
    }

    public function testSetWsdl()
    {
        $server = new Server();

        $this->assertNull($server->getWSDL());
        $server->setWSDL(__DIR__.'/TestAsset/_files/wsdl_example.wsdl');
        $this->assertEquals(__DIR__.'/TestAsset/_files/wsdl_example.wsdl', $server->getWSDL());

        //$this->setExpectedException('Laminas\Soap\Exception\InvalidArgumentException', 'foo');
        $server->setWSDL(__DIR__ . '/TestAsset/_files/wsdl_example.wsdl');
    }

    public function testGetWsdl()
    {
        $server = new Server();

        $this->assertNull($server->getWSDL());
        $server->setWSDL(__DIR__ . '/TestAsset/_files/wsdl_example.wsdl');
        $this->assertEquals(__DIR__ . '/TestAsset/_files/wsdl_example.wsdl', $server->getWSDL());
    }

    public function testAddFunction()
    {
        $this->markTestSkipped();
        $server = new Server();

        // Correct function should pass
        $server->addFunction('\LaminasTest\Soap\TestAsset\TestFunc');

        // Array of correct functions should pass
        $functions = ['\LaminasTest\Soap\TestAsset\TestFunc2',
            '\LaminasTest\Soap\TestAsset\TestFunc3',
            '\LaminasTest\Soap\TestAsset\TestFunc4'];
        $server->addFunction($functions);

        $this->assertEquals(
            array_merge(['\LaminasTest\Soap\TestAsset\TestFunc'], $functions),
            $server->getFunctions()
        );
    }

    public function testAddBogusFunctionAsInteger()
    {
        $server = new Server();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid function specified');
        $server->addFunction(126);
    }

    public function testAddBogusFunctionsAsString()
    {
        $server = new Server();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid function specified');
        $server->addFunction('bogus_function');
    }

    public function testAddBogusFunctionsAsArray()
    {
        $server = new Server();

        $functions = [
            '\TestAsset\TestFunc5',
            'bogus_function',
            '\TestAsset\TestFunc6'
        ];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('One or more invalid functions specified in array');
        $server->addFunction($functions);
    }

    public function testAddAllFunctionsSoapConstant()
    {
        $server = new Server();

        // SOAP_FUNCTIONS_ALL as a value should pass
        $server->addFunction(SOAP_FUNCTIONS_ALL);
        $server->addFunction('substr');
        $this->assertEquals([SOAP_FUNCTIONS_ALL], $server->getFunctions());
    }

    public function testSetClass()
    {
        $server = new Server();

        // Correct class name should pass
        $r = $server->setClass(TestAsset\ServerTestClass::class);
        $this->assertSame($server, $r);
    }

    /**
     * @group PR-706
     */
    public function testSetClassWithObject()
    {
        $server = new Server();

        // Correct class name should pass
        $object = new ServerTestClass();
        $r = $server->setClass($object);
        $this->assertSame($server, $r);
    }

    public function testSetClassTwiceThrowsException()
    {
        $server = new Server();
        $server->setClass(TestAsset\ServerTestClass::class);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('A class has already been registered with this soap server instance');
        $server->setClass(TestAsset\ServerTestClass::class);
    }

    public function testSetClassWithArguments()
    {
        $server = new Server();

        // Correct class name should pass
        $r = $server->setClass(TestAsset\ServerTestClass::class, null, 1, 2, 3, 4);
        $this->assertSame($server, $r);
    }

    public function testSetBogusClassWithIntegerName()
    {
        $server = new Server();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid class argument (integer)');
        $server->setClass(465);
    }

    public function testSetBogusClassWithUnknownClassName()
    {
        $server = new Server();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Class "Laminas_Soap_Server_Test_BogusClass" does not exist');
        $server->setClass('Laminas_Soap_Server_Test_BogusClass');
    }

    /**
     * @group Laminas-4366
     */
    public function testSetObject()
    {
        $server = new Server();

        // Correct class name should pass
        $r = $server->setObject(new TestAsset\ServerTestClass());
        $this->assertSame($server, $r);
    }

    /**
     * @group Laminas-4366
     */
    public function testSetObjectThrowsExceptionWithBadInput1()
    {
        $server = new Server();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid object argument (integer)');
        $server->setObject(465);
    }

    /**
     * @group Laminas-4366
     */
    public function testSetObjectThrowsExceptionWithBadInput2()
    {
        $server = new Server();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid object argument (integer)');
        $int = 1;
        $server->setObject($int);
    }

    public function testGetFunctions()
    {
        $this->markTestSkipped();
        $server = new Server();

        $server->addFunction('\LaminasTest\Soap\TestAsset\TestFunc');

        $functions  = ['\LaminasTest\Soap\TestAsset\TestFunc2',
            '\LaminasTest\Soap\TestAsset\TestFunc3',
            '\LaminasTest\Soap\TestAsset\TestFunc4'];
        $server->addFunction($functions);

        $functions  = ['\LaminasTest\Soap\TestAsset\TestFunc3',
            '\LaminasTest\Soap\TestAsset\TestFunc5',
            '\LaminasTest\Soap\TestAsset\TestFunc6'];
        $server->addFunction($functions);

        $allAddedFunctions = [
            '\LaminasTest\Soap\TestAsset\TestFunc',
            '\LaminasTest\Soap\TestAsset\TestFunc2',
            '\LaminasTest\Soap\TestAsset\TestFunc3',
            '\LaminasTest\Soap\TestAsset\TestFunc4',
            '\LaminasTest\Soap\TestAsset\TestFunc5',
            '\LaminasTest\Soap\TestAsset\TestFunc6'
        ];
        $this->assertEquals($allAddedFunctions, $server->getFunctions());
    }


    public function testGetFunctionsWithClassAttached()
    {
        $server = new Server();
        $server->setClass(TestAsset\ServerTestClass::class);

        $this->assertEquals(
            ['testFunc1', 'testFunc2', 'testFunc3', 'testFunc4', 'testFunc5'],
            $server->getFunctions()
        );
    }

    public function testGetFunctionsWithObjectAttached()
    {
        $server = new Server();
        $server->setObject(new ServerTestClass());

        $this->assertEquals(
            ['testFunc1', 'testFunc2', 'testFunc3', 'testFunc4', 'testFunc5'],
            $server->getFunctions()
        );
    }

    public function testSetPersistence()
    {
        $server = new Server();

        $this->assertNull($server->getPersistence());
        $server->setPersistence(SOAP_PERSISTENCE_SESSION);
        $this->assertEquals(SOAP_PERSISTENCE_SESSION, $server->getPersistence());

        $server->setPersistence(SOAP_PERSISTENCE_REQUEST);
        $this->assertEquals(SOAP_PERSISTENCE_REQUEST, $server->getPersistence());
    }

    public function testSetUnknownPersistenceStateThrowsException()
    {
        $server = new Server();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid persistence mode specified');
        $server->setPersistence('bogus');
    }

    public function testGetPersistence()
    {
        $server = new Server();

        $this->assertNull($server->getPersistence());
        $server->setPersistence(SOAP_PERSISTENCE_SESSION);
        $this->assertEquals(SOAP_PERSISTENCE_SESSION, $server->getPersistence());
    }

    public function testGetLastRequest()
    {
        if (headers_sent()) {
            $this->markTestSkipped(sprintf(
                'Cannot run %s() when headers have already been sent; '
                . 'enable output buffering to run this test',
                __METHOD__
            ));
        }

        $server = new Server();
        $server->setOptions(['location' => 'test://', 'uri' => 'https://getlaminas.org']);
        $server->setReturnResponse(true);

        $server->setClass(TestAsset\ServerTestClass::class);

        $request =
            '<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" '
            . 'xmlns:ns1="https://getlaminas.org" '
            . 'xmlns:xsd="http://www.w3.org/2001/XMLSchema" '
            . 'xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" '
            . 'xmlns:SOAP-ENC="http://schemas.xmlsoap.org/soap/encoding/" '
            . 'SOAP-ENV:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/">'
            .     '<SOAP-ENV:Body>'
            .         '<ns1:testFunc2>'
            .             '<param0 xsi:type="xsd:string">World</param0>'
            .         '</ns1:testFunc2>'
            .     '</SOAP-ENV:Body>'
            . '</SOAP-ENV:Envelope>';

        $server->handle($request);

        $this->assertEquals($request, $server->getLastRequest());
    }

    public function testSetReturnResponse()
    {
        $server = new Server();

        $this->assertFalse($server->getReturnResponse());

        $server->setReturnResponse(true);
        $this->assertTrue($server->getReturnResponse());

        $server->setReturnResponse(false);
        $this->assertFalse($server->getReturnResponse());
    }

    public function testGetReturnResponse()
    {
        $server = new Server();

        $this->assertFalse($server->getReturnResponse());

        $server->setReturnResponse(true);
        $this->assertTrue($server->getReturnResponse());
    }

    public function testGetLastResponse()
    {
        if (headers_sent()) {
            $this->markTestSkipped(sprintf(
                'Cannot run %s() when headers have already been sent; '
                . 'enable output buffering to run this test',
                __METHOD__
            ));
        }

        $server = new Server();
        $server->setOptions(['location' => 'test://', 'uri' => 'https://getlaminas.org']);
        $server->setReturnResponse(true);

        $server->setClass(TestAsset\ServerTestClass::class);

        $request =
            '<?xml version="1.0" encoding="UTF-8"?>' . "\n"
            . '<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" '
            . 'xmlns:ns1="https://getlaminas.org" '
            . 'xmlns:xsd="http://www.w3.org/2001/XMLSchema" '
            . 'xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" '
            . 'xmlns:SOAP-ENC="http://schemas.xmlsoap.org/soap/encoding/" '
            . 'SOAP-ENV:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/">'
            .     '<SOAP-ENV:Body>'
            .         '<ns1:testFunc2>'
            .             '<param0 xsi:type="xsd:string">World</param0>'
            .         '</ns1:testFunc2>'
            .     '</SOAP-ENV:Body>'
            . '</SOAP-ENV:Envelope>' . "\n";

        $expectedResponse =
            '<?xml version="1.0" encoding="UTF-8"?>' . "\n"
            . '<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" '
            . 'xmlns:ns1="https://getlaminas.org" '
            . 'xmlns:xsd="http://www.w3.org/2001/XMLSchema" '
            . 'xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" '
            . 'xmlns:SOAP-ENC="http://schemas.xmlsoap.org/soap/encoding/" '
            . 'SOAP-ENV:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/">'
            .     '<SOAP-ENV:Body>'
            .         '<ns1:testFunc2Response>'
            .             '<return xsi:type="xsd:string">Hello World!</return>'
            .         '</ns1:testFunc2Response>'
            .     '</SOAP-ENV:Body>'
            . '</SOAP-ENV:Envelope>' . "\n";

        $server->handle($request);

        $this->assertEquals($expectedResponse, $server->getResponse());
    }

    public function testHandle()
    {
        if (!extension_loaded('soap')) {
            $this->markTestSkipped('Soap extension not loaded');
        }

        if (headers_sent()) {
            $this->markTestSkipped(sprintf(
                'Cannot run %s() when headers have already been sent; '
                . 'enable output buffering to run this test',
                __METHOD__
            ));
        }

        $server = new Server();
        $server->setOptions(['location' => 'test://', 'uri' => 'https://getlaminas.org']);

        $server->setClass(TestAsset\ServerTestClass::class);

        $localClient = new TestLocalSoapClient(
            $server,
            null,
            [
                'location' => 'test://',
                'uri' => 'https://getlaminas.org'
            ]
        );

        // Local SOAP client call automatically invokes handle method of the provided SOAP server
        $this->assertEquals('Hello World!', $localClient->testFunc2('World'));


        $request =
            '<?xml version="1.0" encoding="UTF-8"?>' . "\n"
            . '<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" '
            . 'xmlns:ns1="https://getlaminas.org" '
            . 'xmlns:xsd="http://www.w3.org/2001/XMLSchema" '
            . 'xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" '
            . 'xmlns:SOAP-ENC="http://schemas.xmlsoap.org/soap/encoding/" '
            . 'SOAP-ENV:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/">'
            .     '<SOAP-ENV:Body>'
            .         '<ns1:testFunc2>'
            .             '<param0 xsi:type="xsd:string">World</param0>'
            .         '</ns1:testFunc2>'
            .     '</SOAP-ENV:Body>'
            . '</SOAP-ENV:Envelope>' . "\n";

        $expectedResponse =
            '<?xml version="1.0" encoding="UTF-8"?>' . "\n"
            . '<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" '
            . 'xmlns:ns1="https://getlaminas.org" '
            . 'xmlns:xsd="http://www.w3.org/2001/XMLSchema" '
            . 'xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" '
            . 'xmlns:SOAP-ENC="http://schemas.xmlsoap.org/soap/encoding/" '
            . 'SOAP-ENV:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/">'
            .     '<SOAP-ENV:Body>'
            .         '<ns1:testFunc2Response>'
            .             '<return xsi:type="xsd:string">Hello World!</return>'
            .         '</ns1:testFunc2Response>'
            .     '</SOAP-ENV:Body>'
            . '</SOAP-ENV:Envelope>' . "\n";

        $server1 = new Server();
        $server1->setOptions(['location' => 'test://', 'uri' => 'https://getlaminas.org']);

        $server1->setClass(TestAsset\ServerTestClass::class);
        $server1->setReturnResponse(true);

        $this->assertEquals($expectedResponse, $server1->handle($request));
    }

    public function testEmptyRequest()
    {
        $server = new Server();
        $server->setOptions(['location' => 'test://', 'uri' => 'https://getlaminas.org']);
        $server->setReturnResponse(true);

        $server->setClass(TestAsset\ServerTestClass::class);

        $request = '';
        $response = $server->handle($request);

        $this->assertStringContainsString('Empty request', $response->getMessage());
    }

    /**
     * @dataProvider dataProviderForRegisterFaultException
     *
     * @param string|array $exception
     */
    public function testRegisterFaultException($exception)
    {
        $server = new Server();

        $server->registerFaultException($exception);

        if (!is_array($exception)) {
            $this->assertContains($exception, $server->getFaultExceptions());
        } else {
            foreach ($exception as $row) {
                $this->assertContains($row, $server->getFaultExceptions());
            }
        }
    }

    /**
     * @dataProvider dataProviderForRegisterFaultException
     *
     * @param string|array $exception
     */
    public function testDeregisterFaultException($exception)
    {
        $server = new Server();

        $server->registerFaultException($exception);
        if (is_array($exception)) {
            $exception = array_shift($exception);
        }

        $this->assertTrue($server->deregisterFaultException($exception));

        $this->assertNotContains($exception, $server->getFaultExceptions());
    }

    /**
     * @dataProvider dataProviderForRegisterFaultException
     *
     * @param string|array $exception
     */
    public function testIsRegisteredAsFaultException($exception)
    {
        $server = new Server();
        $server->registerFaultException($exception);


        if (! is_array($exception)) {
            $this->assertTrue($server->isRegisteredAsFaultException($exception));
        } else {
            foreach ($exception as $row) {
                $this->assertTrue($server->isRegisteredAsFaultException($row));
            }
        }
    }

    /**
     * @return array
     */
    public function dataProviderForRegisterFaultException()
    {
        return [
            ['Exception'],
            ['Magento\Webapi\Model\Laminas\Soap\Exception\InvalidArgumentException'],
            ['InvalidArgumentException'],
            ['Laminas\Server\Exception\RuntimeException'],
            [['Laminas\Server\Exception\RuntimeException']],
            [['Laminas\Server\Exception\RuntimeException', 'InvalidArgumentException']],
        ];
    }

    public function testFaultWithTextMessage()
    {
        $server = new Server();
        $fault = $server->fault('FaultMessage!');

        $this->assertInstanceOf('SoapFault', $fault);
        $this->assertStringContainsString('FaultMessage!', $fault->getMessage());
    }

    public function testFaultWithUnregisteredException()
    {
        $server = new Server();
        $fault = $server->fault(new Exception('MyException'));

        $this->assertInstanceOf('SoapFault', $fault);
        $this->assertStringContainsString('Unknown error', $fault->getMessage());
        $this->assertStringNotContainsString('MyException', $fault->getMessage());
    }

    public function testFaultWithRegisteredException()
    {
        $server = new Server();
        $server->registerFaultException('\Magento\Webapi\Model\Laminas\Soap\Exception\RuntimeException');
        $server->registerFaultException('\Magento\Webapi\Model\Laminas\Soap\Exception\InvalidArgumentException');
        $fault = $server->fault(new RuntimeException('MyException'));
        $this->assertInstanceOf('SoapFault', $fault);
        $this->assertStringNotContainsString('Unknown error', $fault->getMessage());
        $this->assertStringContainsString('MyException', $fault->getMessage());
    }

    public function testFaultWithBogusInput()
    {
        $server = new Server();
        $fault = $server->fault(['Here', 'There', 'Bogus']);

        $this->assertStringContainsString('Unknown error', $fault->getMessage());
    }

    /**
     * @group Laminas-3958
     */
    public function testFaultWithIntegerFailureCodeDoesNotBreakClassSoapFault()
    {
        $server = new Server();
        $fault = $server->fault("FaultMessage!", 5000);

        $this->assertInstanceOf('SoapFault', $fault);
    }

    /**
     * @expectedException SoapFault
     */
    public function testHandlePhpErrors()
    {
        if (headers_sent()) {
            $this->markTestSkipped(sprintf(
                'Cannot run %s() when headers have already been sent; '
                . 'enable output buffering to run this test',
                __METHOD__
            ));
        }

        $wsdlFilename = __DIR__ . '/TestAsset/_files/testHandlePhpErrors.wsdl';
        $autodiscover = new AutoDiscover();
        $autodiscover->setOperationBodyStyle([
            'use'           => 'literal',
        ]);

        $autodiscover->setBindingStyle([
            'style'         => 'document',
            'transport'     => 'http://schemas.xmlsoap.org/soap/http'
        ]);


        $autodiscover->setServiceName('ExampleService');
        $autodiscover->setUri('http://example.com');


        $autodiscover->setClass(TestAsset\errorClass::class);

        $wsdl = $autodiscover->generate();
        $wsdl->dump($wsdlFilename);

        $server = new Server($wsdlFilename);

        $server->setClass(TestAsset\errorClass::class);

        $client = new Local($server, $wsdlFilename);
        $client->triggerError();
        unlink($wsdlFilename);
    }

    public function testLoadFunctionsIsNotImplemented()
    {
        $server = new Server();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unimplemented method');
        $server->loadFunctions("bogus");
    }

    public function testErrorHandlingOfSoapServerChangesToThrowingSoapFaultWhenInHandleMode()
    {
        if (headers_sent()) {
            $this->markTestSkipped(sprintf(
                'Cannot run %s() when headers have already been sent; '
                . 'enable output buffering to run this test',
                __METHOD__
            ));
        }

        $server = new Server();
        $server->setOptions(['location' => 'test://', 'uri' => 'https://getlaminas.org']);
        $server->setReturnResponse(true);

        // Requesting Method with enforced parameter without it.
        $request =
            '<?xml version="1.0" encoding="UTF-8"?>' . "\n"
            . '<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" '
            . 'xmlns:ns1="https://getlaminas.org" '
            . 'xmlns:xsd="http://www.w3.org/2001/XMLSchema" '
            . 'xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" '
            . 'xmlns:SOAP-ENC="http://schemas.xmlsoap.org/soap/encoding/" '
            . 'SOAP-ENV:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/">'
            .     '<SOAP-ENV:Body>'
            .         '<ns1:testFunc5 />'
            .     '</SOAP-ENV:Body>'
            . '</SOAP-ENV:Envelope>' . "\n";

        $server->setClass(TestAsset\ServerTestClass::class);
        $response = $server->handle($request);

        $this->assertStringContainsString(
            '<SOAP-ENV:Fault><faultcode>Receiver</faultcode><faultstring>Test Message</faultstring></SOAP-ENV:Fault>',
            $response
        );
    }

    /**
     * @group Laminas-5597
     */
    public function testServerAcceptsLaminasConfigObject()
    {
        $options = ['soap_version' => SOAP_1_1,
            'actor' => 'https://getlaminas.org/Laminas_Soap_ServerTest.php',
            'classmap'       => [
                'TestData1' => TestAsset\TestData1::class,
                'TestData2' =>  TestAsset\TestData2::class
            ],
            'encoding' => 'ISO-8859-1',
            'uri' => 'https://getlaminas.org/Laminas_Soap_ServerTest.php'
        ];
        $config = new Config($options);

        $server = new Server();
        $server->setOptions($config);
        $this->assertEquals($options, $server->getOptions());
    }

    /**
     * @group Laminas-5300
     */
    public function testSetAndGetFeatures()
    {
        $server = new Server();
        $this->assertNull($server->getSoapFeatures());
        $server->setSoapFeatures(100);
        $this->assertEquals(100, $server->getSoapFeatures());
        $options = $server->getOptions();
        $this->assertTrue(isset($options['features']));
        $this->assertEquals(100, $options['features']);
    }

    /**
     * @group Laminas-5300
     */
    public function testSetAndGetWSDLCache()
    {
        $server = new Server();
        $this->assertNull($server->getWSDLCache());
        $server->setWSDLCache(100);
        $this->assertEquals(100, $server->getWSDLCache());
        $options = $server->getOptions();
        $this->assertTrue(isset($options['cache_wsdl']));
        $this->assertEquals(100, $options['cache_wsdl']);
    }

    public function testSetAndGetParseHuge()
    {
        $server = new Server();
        $this->assertNull($server->getParseHuge());
        $server->setParseHuge(true);
        $this->assertEquals(true, $server->getParseHuge());
        $options = $server->getOptions();
        $this->assertTrue(isset($options['parse_huge']));
        $this->assertEquals(true, $options['parse_huge']);
    }

    /**
     * @group Laminas-11411
     */
    public function testHandleUsesProperRequestParameter()
    {
        $server = new MockServer();
        $r = $server->handle(new DOMDocument('1.0', 'UTF-8'));
        $this->assertIsString($server->mockSoapServer->handle[0]);
    }

    /**
     * @runInSeparateProcess
     */
    public function testShouldThrowExceptionIfHandledRequestContainsDoctype()
    {
        $server = new Server();
        $server->setOptions(['location' => 'test://', 'uri' => 'https://getlaminas.org']);
        $server->setReturnResponse(true);

        $server->setClass(TestAsset\ServerTestClass::class);

        $request =
            '<?xml version="1.0" encoding="UTF-8"?>' . "\n" . '<!DOCTYPE foo>' . "\n"
            . '<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" '
            . 'xmlns:ns1="https://getlaminas.org" '
            . 'xmlns:xsd="http://www.w3.org/2001/XMLSchema" '
            . 'xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" '
            . 'xmlns:SOAP-ENC="http://schemas.xmlsoap.org/soap/encoding/" '
            . 'SOAP-ENV:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/">'
            .     '<SOAP-ENV:Body>'
            .         '<ns1:testFunc2>'
            .             '<param0 xsi:type="xsd:string">World</param0>'
            .         '</ns1:testFunc2>'
            .     '</SOAP-ENV:Body>'
            . '</SOAP-ENV:Envelope>' . "\n";
        $response = $server->handle($request);

        $this->assertStringContainsString('Invalid XML', $response->getMessage());
    }

    public function testDebugMode()
    {
        $server = new Server();
        $beforeDebug = $server->fault(new Exception('test'));
        $server->setDebugMode(true);
        $afterDebug = $server->fault(new Exception('test'));

        $this->assertEquals('Unknown error', $beforeDebug->getMessage());
        $this->assertEquals('test', $afterDebug->getMessage());
    }

    public function testGetOriginalCaughtException()
    {
        $server = new Server();
        $fault = $server->fault(new Exception('test'));

        $exception = $server->getException();
        $this->assertInstanceOf('\Exception', $exception);
        $this->assertEquals('test', $exception->getMessage());
        $this->assertInstanceOf('\SoapFault', $fault);
        $this->assertEquals('Unknown error', $fault->getMessage());
    }

    public function testGetSoapInternalInstance()
    {
        $server = new Server();
        $server->setOptions(['location' => 'test://', 'uri' => 'https://getlaminas.org']);
        $internalServer = $server->getSoap();
        $this->assertInstanceOf('\SoapServer', $internalServer);
        $this->assertSame($internalServer, $server->getSoap());
    }

    public function testDisableEntityLoaderAfterException()
    {
        $server = new Server();
        $server->setOptions(['location' => 'test://', 'uri' => 'https://getlaminas.org']);
        $server->setReturnResponse(true);
        $server->setClass(TestAsset\ServerTestClass::class);

        $loadEntities = true;
        if (LIBXML_VERSION < 20900) {
            $loadEntities = libxml_disable_entity_loader(false);
        }

        // Doing a request that is guaranteed to cause an exception in Server::_setRequest():
        $invalidRequest = '---';
        $response = @$server->handle($invalidRequest);

        // Sanity check; making sure that an exception has been triggered:
        $this->assertInstanceOf('\SoapFault', $response);

        if (LIBXML_VERSION < 20900) {
            // The "disable entity loader" setting should be restored to "false" after the exception is raised:
            $this->assertFalse(libxml_disable_entity_loader());

            // Cleanup; restoring original setting:
            libxml_disable_entity_loader($loadEntities);
        }
    }
}
