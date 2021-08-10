<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Webapi\Test\Unit\Model\Soap\Wsdl;

use DOMDocument;
use Magento\Webapi\Model\Laminas\Soap\Wsdl;
use Magento\Framework\Reflection\TypeProcessor;
use Magento\Webapi\Model\Soap\Wsdl\ComplexTypeStrategy;
use PHPUnit\Framework\MockObject\MockObject;

use PHPUnit\Framework\TestCase;

/**
 * Complex type strategy tests.
 */
class ComplexTypeStrategyTest extends TestCase
{
    /**
     * @var TypeProcessor|MockObject
     */
    protected $_typeProcessor;

    /**
     * @var Wsdl|MockObject
     */
    protected $_wsdl;

    /**
     * @var ComplexTypeStrategy
     */
    protected $_strategy;

    /**
     * @inheirtDoc
     */
    protected function setUp(): void
    {
        $this->_typeProcessor = $this->getMockBuilder(TypeProcessor::class)
            ->onlyMethods(['getTypeData'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->_wsdl = $this->getMockBuilder(\Magento\Webapi\Model\Soap\Wsdl::class)
            ->onlyMethods(['toDomDocument', 'getTypes', 'getSchema'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->_strategy = new ComplexTypeStrategy($this->_typeProcessor);
        $this->_strategy->setContext($this->_wsdl);

        parent::setUp();
    }

    /**
     * @inheirtDoc
     */
    protected function tearDown(): void
    {
        unset($this->_typeProcessor);
        unset($this->_strategy);
        unset($this->_wsdl);

        parent::tearDown();
    }

    /**
     * Test that addComplexType returns type WSDL name
     * if it has already been processed (registered at includedTypes in WSDL).
     *
     * @return void
     */
    public function testCheckTypeName(): void
    {
        $testType = 'testComplexTypeName';
        $testTypeWsdlName = 'tns:' . $testType;
        $includedTypes = [$testType => $testTypeWsdlName];
        $this->_wsdl->expects($this->exactly(2))->method('getTypes')->willReturn($includedTypes);

        $this->assertEquals($testTypeWsdlName, $this->_strategy->addComplexType($testType));
    }

    /**
     * Test adding complex type with simple parameters.
     *
     * @param string $type
     * @param array $data
     * @dataProvider addComplexTypeDataProvider
     */
    public function testAddComplexTypeSimpleParameters($type, $data)
    {
        $this->_wsdl->expects($this->any())->method('getTypes')->willReturn([]);

        $this->_wsdl->expects($this->any())->method('toDomDocument')->willReturn(new DOMDocument());

        $schemaMock = $this->getMockBuilder(\DOMElement::class)
            ->setConstructorArgs(['a'])
            ->getMock();
        $schemaMock->expects($this->any())->method('appendChild');
        $this->_wsdl->expects($this->any())->method('getSchema')->willReturn($schemaMock);

        $this->_typeProcessor->expects(
            $this->at(0)
        )->method(
            'getTypeData'
        )->with(
            $type
        )->willReturn(
            $data
        );

        $this->assertEquals(Wsdl::TYPES_NS . ':' . $type, $this->_strategy->addComplexType($type));
    }

    /**
     * Data provider for testAddComplexTypeSimpleParameters().
     *
     * @return array
     */
    public static function addComplexTypeDataProvider(): array
    {
        return [
            'simple parameters' => [
                'VendorModuleADataStructure',
                [
                    'documentation' => 'test',
                    'parameters' => [
                        'string_param' => [
                            'type' => 'string',
                            'required' => true,
                            'documentation' => 'Required string param.'
                        ],
                        'int_param' => [
                            'type' => 'int',
                            'required' => true,
                            'documentation' => 'Required int param.'
                        ],
                        'bool_param' => [
                            'type' => 'boolean',
                            'required' => false,
                            'documentation' => 'Optional complex type param.{annotation:test}'
                        ]
                    ]
                ]
            ],
            'type with call info' => [
                'VendorModuleADataStructure',
                [
                    'documentation' => 'test',
                    'parameters' => [
                        'string_param' => [
                            'type' => 'string',
                            'required' => false,
                            'documentation' => '{callInfo:VendorModuleACreate:requiredInput:conditionally}'
                        ]
                    ],
                    'callInfo' => [
                        'requiredInput' => ['yes' => ['calls' => ['VendorModuleACreate']]],
                        'returned' => ['always' => ['calls' => ['VendorModuleAGet']]]
                    ]
                ]
            ],
            'parameter with call info' => [
                'VendorModuleADataStructure',
                [
                    'documentation' => 'test',
                    'parameters' => [
                        'string_param' => [
                            'type' => 'string',
                            'required' => false,
                            'documentation' => '{callInfo:VendorModuleACreate:requiredInput:conditionally}' .
                            '{callInfo:allCallsExcept(VendorModuleAGet):returned:always}'
                        ]
                    ]
                ]
            ],
            'parameter with see link' => [
                'VendorModuleADataStructure',
                [
                    'documentation' => 'test',
                    'parameters' => [
                        'string_param' => [
                            'type' => 'string',
                            'required' => false,
                            'documentation' => '{seeLink:http://google.com/:title:for}'
                        ]
                    ]
                ]
            ],
            'parameter with doc instructions' => [
                'VendorModuleADataStructure',
                [
                    'documentation' => 'test',
                    'parameters' => [
                        'string_param' => [
                            'type' => 'string',
                            'required' => false,
                            'documentation' => '{docInstructions:output:noDoc}'
                        ]
                    ]
                ]
            ]
        ];
    }

    /**
     * Test adding complex type with complex parameters and arrays.
     *
     * @return void
     */
    public function testAddComplexTypeComplexParameters(): void
    {
        $type = 'VendorModuleADataStructure';
        $parameterType = 'ComplexType';
        $typeData = [
            'documentation' => 'test',
            'parameters' => [
                'complex_param' => [
                    'type' => $parameterType,
                    'required' => true,
                    'documentation' => 'complex type param.'
                ]
            ]
        ];
        $parameterData = [
            'documentation' => 'test',
            'parameters' => [
                'string_param' => [
                    'type' => 'ComplexTypeB[]',
                    'required' => true,
                    'documentation' => 'string param.'
                ]
            ]
        ];

        $this->_wsdl->expects($this->at(0))->method('getTypes')->willReturn([]);
        $this->_wsdl->expects($this->any())
            ->method('getTypes')
            ->willReturn([$type => Wsdl::TYPES_NS . ':' . $type]);

        $this->_wsdl->expects($this->any())->method('toDomDocument')->willReturn(new DOMDocument());
        $schemaMock = $this->getMockBuilder(\DOMElement::class)
            ->setConstructorArgs(['a'])
            ->getMock();
        $schemaMock->expects($this->any())->method('appendChild');
        $this->_wsdl->expects($this->any())->method('getSchema')->willReturn($schemaMock);
        $this->_typeProcessor
            ->method('getTypeData')
            ->withConsecutive([$type], [$parameterType])
            ->willReturnOnConsecutiveCalls(
            $typeData, $parameterData
        );

        $this->assertEquals(Wsdl::TYPES_NS . ':' . $type, $this->_strategy->addComplexType($type));
    }

    /**
     * Test to verify if annotations are added correctly.
     *
     * @return void
     */
    public function testAddAnnotationToComplexType(): void
    {
        $dom = new DOMDocument();
        $this->_wsdl->expects($this->any())->method('toDomDocument')->willReturn($dom);
        $annotationDoc = "test doc";
        $complexType = $dom->createElement(Wsdl::XSD_NS . ':complexType');
        $complexType->setAttribute('name', 'testRequest');
        $this->_strategy->addAnnotation($complexType, $annotationDoc);
        $this->assertEquals(
            $annotationDoc,
            $complexType->getElementsByTagName("xsd:documentation")->item(0)->nodeValue
        );
    }
}
