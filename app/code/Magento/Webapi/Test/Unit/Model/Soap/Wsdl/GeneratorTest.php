<?php declare(strict_types=1);
/**
 * Tests for \Magento\Webapi\Model\Soap\Wsdl\Generator.
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Webapi\Test\Unit\Model\Soap\Wsdl;

use Magento\Eav\Model\TypeLocator;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Reflection\TypeProcessor;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Webapi\Authorization;
use Magento\Framework\Webapi\CustomAttributeTypeLocatorInterface;
use Magento\Framework\Webapi\Exception;
use Magento\Webapi\Model\Cache\Type\Webapi;
use Magento\Webapi\Model\ServiceMetadata;
use Magento\Webapi\Model\Soap\Wsdl;
use Magento\Webapi\Model\Soap\Wsdl\Generator;
use Magento\Webapi\Model\Soap\WsdlFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class GeneratorTest extends TestCase
{
    /**  @var Generator */
    protected $_wsdlGenerator;

    /**
     * @var CustomAttributeTypeLocatorInterface|MockObject
     */
    protected $customAttributeTypeLocator = null;

    /**  @var ServiceMetadata|MockObject */
    protected $serviceMetadata;

    /**  @var WsdlFactory|MockObject */
    protected $_wsdlFactoryMock;

    /** @var Webapi|MockObject */
    protected $_cacheMock;

    /** @var TypeProcessor|MockObject */
    protected $_typeProcessor;

    /**
     * @var MockObject
     */
    private $serializer;

    protected function setUp(): void
    {
        $this->serviceMetadata = $this->getMockBuilder(
            ServiceMetadata::class
        )->disableOriginalConstructor()
            ->getMock();

        $_wsdlMock = $this->getMockBuilder(
            Wsdl::class
        )->disableOriginalConstructor()
            ->setMethods(
                [
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
                    'toXML',
                ]
            )->getMock();
        $this->_wsdlFactoryMock = $this->getMockBuilder(
            WsdlFactory::class
        )->setMethods(
            ['create']
        )->disableOriginalConstructor()
            ->getMock();
        $this->_wsdlFactoryMock->expects($this->any())->method('create')->willReturn($_wsdlMock);

        $this->_cacheMock = $this->getMockBuilder(
            Webapi::class
        )->disableOriginalConstructor()
            ->getMock();
        $this->_cacheMock->expects($this->any())->method('load')->willReturn(false);
        $this->_cacheMock->expects($this->any())->method('save')->willReturn(true);

        $this->_typeProcessor = $this->createMock(TypeProcessor::class);

        /** @var Authorization|MockObject $authorizationMock */
        $authorizationMock = $this->getMockBuilder(Authorization::class)
            ->disableOriginalConstructor()
            ->getMock();
        $authorizationMock->expects($this->any())->method('isAllowed')->willReturn(true);

        $objectManager = new ObjectManager($this);

        $this->customAttributeTypeLocator = $objectManager
            ->getObject(TypeLocator::class);

        $this->serializer = $this->getMockBuilder(Json::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->serializer->expects($this->any())
            ->method('serialize')
            ->willReturnCallback(
                function ($value) {
                    return json_encode($value);
                }
            );
        $objectManagerMock = $this->getMockForAbstractClass(ObjectManagerInterface::class);
        $objectManagerMock->expects($this->any())
            ->method('get')
            ->willReturnMap([
                [Json::class, $this->serializer]
            ]);
        \Magento\Framework\App\ObjectManager::setInstance($objectManagerMock);

        $this->_wsdlGenerator = $objectManager->getObject(
            Generator::class,
            [
                'wsdlFactory' => $this->_wsdlFactoryMock,
                'cache' => $this->_cacheMock,
                'typeProcessor' => $this->_typeProcessor,
                'customAttributeTypeLocator' => $this->customAttributeTypeLocator,
                'serviceMetadata' => $this->serviceMetadata,
                'authorization' => $authorizationMock,
                'serializer' => $this->serializer
            ]
        );

        parent::setUp();
    }

    /**
     * Test getElementComplexTypeName
     */
    public function testGetElementComplexTypeName()
    {
        $this->assertEquals("Test", $this->_wsdlGenerator->getElementComplexTypeName("test"));
    }

    /**
     * Test getPortTypeName
     */
    public function testGetPortTypeName()
    {
        $this->assertEquals("testPortType", $this->_wsdlGenerator->getPortTypeName("test"));
    }

    /**
     * Test getBindingName
     */
    public function testGetBindingName()
    {
        $this->assertEquals("testBinding", $this->_wsdlGenerator->getBindingName("test"));
    }

    /**
     * Test getPortName
     */
    public function testGetPortName()
    {
        $this->assertEquals("testPort", $this->_wsdlGenerator->getPortName("test"));
    }

    /**
     * test getServiceName
     */
    public function testGetServiceName()
    {
        $this->assertEquals("testService", $this->_wsdlGenerator->getServiceName("test"));
    }

    /**
     * @test
     */
    public function testGetInputMessageName()
    {
        $this->assertEquals("operationNameRequest", $this->_wsdlGenerator->getInputMessageName("operationName"));
    }

    /**
     * @test
     */
    public function testGetOutputMessageName()
    {
        $this->assertEquals("operationNameResponse", $this->_wsdlGenerator->getOutputMessageName("operationName"));
    }

    /**
     * Test exception for handle
     *
     * @covers \Magento\Webapi\Model\AbstractSchemaGenerator::generate()
     */
    public function testHandleWithException()
    {
        $this->expectException('Magento\Framework\Webapi\Exception');
        $this->expectExceptionMessage('exception message');
        $genWSDL = 'generatedWSDL';
        $exceptionMsg = 'exception message';
        $requestedService = ['catalogProduct'];
        $serviceMetadata = ['methods' => ['methodName' => ['interface' => 'aInterface', 'resources' => ['anonymous']]]];

        $this->serviceMetadata->expects($this->any())
            ->method('getServiceMetadata')
            ->willReturn($serviceMetadata);
        $this->_typeProcessor->expects($this->once())
            ->method('processInterfaceCallInfo')
            ->willThrowException(new Exception(__($exceptionMsg)));

        $this->assertEquals(
            $genWSDL,
            $this->_wsdlGenerator->generate($requestedService, 'http://', 'magento.host', '/soap/default')
        );
    }
}
